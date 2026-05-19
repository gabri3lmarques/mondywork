<?php

function extrairResumo($html, $maxChars = 280)
{
    $html = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']\s*/i', ' ', $html);
    $texto = html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8');
    $texto = preg_replace('/\s+/', ' ', trim($texto));

    $pos = mb_stripos($texto, 'Breve Resumo do Projeto:');
    if ($pos !== false) {
        $texto = trim(mb_substr($texto, $pos + mb_strlen('Breve Resumo do Projeto:')));
        $texto = preg_replace('/^[\s:,.-]+/', '', $texto);
    }

    if (mb_strlen($texto) > $maxChars) {
        $corte = mb_strrpos(mb_substr($texto, 0, $maxChars), ' ', 0, 'UTF-8');
        $texto = ($corte !== false ? mb_substr($texto, 0, $corte) : mb_substr($texto, 0, $maxChars)) . '...';
    }

    return trim($texto);
}

function slugify($text)
{
    $text = mb_strtolower($text, 'UTF-8');
    $map = ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    $text = preg_replace('/\s+/', '-', $text);
    return trim($text, '-');
}

function manterConexao(&$pdo, $dbConfig)
{
    try {
        $pdo->query("SELECT 1");
    } catch (Exception $e) {
        if (str_contains($e->getMessage(), '2006') || str_contains($e->getMessage(), 'gone away')) {
            echo " [RECONECTANDO...] ";
            $pdo = conectarBanco($dbConfig);
        } else {
            throw $e;
        }
    }
}

function upsertVaga(PDO $pdo, array $dados): void
{
    static $stmt = null;
    if ($stmt === null) {
        $stmt = $pdo->prepare("
            INSERT INTO vagas (vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, descricao, resumo, publicado_em, status, origem, area)
            VALUES (:id, :titulo, :empresa, :local, :modelo, :url, :desc, :resumo, :publicado, 'ativa', :origem, :area)
            ON DUPLICATE KEY UPDATE status = 'ativa', data_coleta = CURRENT_TIMESTAMP, modelo_trabalho = VALUES(modelo_trabalho), publicado_em = VALUES(publicado_em), origem = VALUES(origem), area = VALUES(area)
        ");
    }

    $stmt->execute([
        ':id'        => $dados['vaga_id_externo'],
        ':titulo'    => $dados['titulo'],
        ':empresa'   => $dados['empresa'],
        ':local'     => $dados['localizacao'] ?? null,
        ':modelo'    => $dados['modelo_trabalho'] ?? null,
        ':url'       => $dados['url_vaga'] ?? null,
        ':desc'      => $dados['descricao'] ?? '',
        ':resumo'    => $dados['resumo'] ?? '',
        ':publicado' => $dados['publicado_em'] ?? null,
        ':origem'    => $dados['origem'] ?? 'nacional',
        ':area'      => $dados['area'] ?? null,
    ]);
}
