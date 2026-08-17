<?php

namespace App\Services;

use Exception;

class AvatarService
{
    private const MAX_SIZE_BYTES = 128 * 1024; // 128 KB
    private const TARGET_DIMENSION = 256; // 256x256 px

    private static array $palette = [
        '#4b41e1', '#0284c7', '#059669', '#d97706',
        '#e11d48', '#7c3aed', '#0d9488', '#ea580c',
        '#2563eb', '#4f46e5', '#0891b2', '#16a34a'
    ];

    /**
     * Processa e comprime uma foto enviada por upload.
     * Retorna o caminho relativo da imagem (ex: /uploads/avatars/avatar_1_162000.webp).
     */
    public static function processUpload(array $file, int $userId): string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Nenhum arquivo válido foi enviado.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no envio do arquivo (código ' . $file['error'] . ').');
        }

        // Valida MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif'
        ];

        if (!isset($allowedMimes[$mimeType])) {
            throw new Exception('Formato de imagem inválido. Envie imagens JPG, PNG ou WebP.');
        }

        // Se a extensão GD não estiver disponível no servidor, salva o arquivo diretamente se <= 128KB
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = $allowedMimes[$mimeType];
            $filename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $uploadDir . '/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return '/uploads/avatars/' . $filename;
            }
            throw new Exception('Não foi possível salvar a imagem enviada.');
        }

        // Cria imagem GD a partir do arquivo
        $sourceImage = null;
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/pjpeg':
                $sourceImage = @imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $sourceImage = @imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $sourceImage = @imagecreatefromwebp($file['tmp_name']);
                break;
            case 'image/gif':
                $sourceImage = @imagecreatefromgif($file['tmp_name']);
                break;
        }

        if (!$sourceImage) {
            throw new Exception('Não foi possível processar a imagem enviada.');
        }

        // Corrige rotação EXIF para fotos de celulares se disponível
        if (function_exists('exif_read_data') && ($mimeType === 'image/jpeg' || $mimeType === 'image/pjpeg')) {
            try {
                $exif = @exif_read_data($file['tmp_name']);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 3:
                            $sourceImage = imagerotate($sourceImage, 180, 0);
                            break;
                        case 6:
                            $sourceImage = imagerotate($sourceImage, -90, 0);
                            break;
                        case 8:
                            $sourceImage = imagerotate($sourceImage, 90, 0);
                            break;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $origW = imagesx($sourceImage);
        $origH = imagesy($sourceImage);

        // Crop quadrado centralizado (1:1)
        $cropSize = min($origW, $origH);
        $srcX = (int) floor(($origW - $cropSize) / 2);
        $srcY = (int) floor(($origH - $cropSize) / 2);

        $targetSize = min(self::TARGET_DIMENSION, $cropSize);
        $destImage = imagecreatetruecolor($targetSize, $targetSize);

        // Preserva transparência se PNG/WebP
        imagealphablending($destImage, false);
        imagesavealpha($destImage, true);
        $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
        imagefilledrectangle($destImage, 0, 0, $targetSize, $targetSize, $transparent);

        imagecopyresampled(
            $destImage,
            $sourceImage,
            0, 0,
            $srcX, $srcY,
            $targetSize, $targetSize,
            $cropSize, $cropSize
        );

        imagedestroy($sourceImage);

        // Garante que o diretório de destino existe
        $uploadDir = dirname(__DIR__, 2) . '/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4));
        $supportsWebp = function_exists('imagewebp');
        $extension = $supportsWebp ? 'webp' : 'jpg';
        $finalPath = $uploadDir . '/' . $filename . '.' . $extension;

        // Comprime a imagem garantindo tamanho <= 128 KB
        $quality = 85;
        $saved = false;

        while ($quality >= 20) {
            ob_start();
            if ($extension === 'webp') {
                imagewebp($destImage, null, $quality);
            } else {
                imagejpeg($destImage, null, $quality);
            }
            $imageData = ob_get_clean();

            if (strlen($imageData) <= self::MAX_SIZE_BYTES || $quality <= 25) {
                file_put_contents($finalPath, $imageData);
                $saved = true;
                break;
            }

            $quality -= 15;
        }

        imagedestroy($destImage);

        if (!$saved || !file_exists($finalPath)) {
            throw new Exception('Erro ao comprimir e salvar a imagem.');
        }

        // Se ainda ficou maior que 128KB por algum motivo extremo, reduz dimensão
        if (filesize($finalPath) > self::MAX_SIZE_BYTES) {
            self::reduceToMaxSize($finalPath, $extension);
        }

        return '/uploads/avatars/' . $filename . '.' . $extension;
    }

    /**
     * Redução adicional de emergência caso a imagem ainda exceda 128KB
     */
    private static function reduceToMaxSize(string $filePath, string $ext): void
    {
        $img = $ext === 'webp' ? @imagecreatefromwebp($filePath) : @imagecreatefromjpeg($filePath);
        if (!$img) return;

        $small = imagescale($img, 180, 180);
        if ($small) {
            if ($ext === 'webp') {
                imagewebp($small, $filePath, 60);
            } else {
                imagejpeg($small, $filePath, 60);
            }
            imagedestroy($small);
        }
        imagedestroy($img);
    }

    /**
     * Retorna o HTML do avatar (com foto ou inicial do nome)
     */
    public static function renderAvatar(?array $user, int $size = 40, string $extraClass = ''): string
    {
        $name = trim($user['nome'] ?? 'Usuário');
        $photo = !empty($user['foto']) ? trim($user['foto']) : null;
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        if ($photo) {
            $webRoot = dirname(__DIR__, 2);
            $localPhotoPath = $webRoot . '/' . ltrim($photo, '/');
            if (file_exists($localPhotoPath)) {
                return '<img src="' . htmlspecialchars($photo, ENT_QUOTES, 'UTF-8') . '" '
                    . 'alt="' . $escapedName . '" '
                    . 'class="user-avatar ' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') . '" '
                    . 'style="width:' . $size . 'px;height:' . $size . 'px;border-radius:50%;object-fit:cover;flex-shrink:0;border:1.5px solid rgba(0,0,0,0.08);">';
            }
        }

        // Fallback: Primeira letra do nome
        $firstLetter = mb_strtoupper(mb_substr($name ?: 'U', 0, 1, 'UTF-8'), 'UTF-8');
        $colorIndex = abs(crc32($name)) % count(self::$palette);
        $bgColor = self::$palette[$colorIndex];
        $fontSize = (int) round($size * 0.45);

        return '<div class="user-avatar user-avatar-fallback ' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') . '" '
            . 'title="' . $escapedName . '" '
            . 'style="width:' . $size . 'px;height:' . $size . 'px;border-radius:50%;background:' . $bgColor . ';'
            . 'color:#ffffff;font-weight:700;font-size:' . $fontSize . 'px;display:inline-flex;'
            . 'align-items:center;justify-content:center;text-transform:uppercase;user-select:none;flex-shrink:0;'
            . 'box-shadow:0 1px 3px rgba(0,0,0,0.12);">'
            . htmlspecialchars($firstLetter, ENT_QUOTES, 'UTF-8')
            . '</div>';
    }
}
