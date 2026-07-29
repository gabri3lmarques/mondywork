<?php

namespace App\Services;

use PDO;
use Exception;

class VagaPremiumService
{
    private PDO $pdo;
    private EfibankService $efibankService;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->efibankService = new EfibankService();
    }

    /**
     * Cadastrar nova vaga e gerar cobrança Pix no PagBank
     */
    public function criarVagaEGerarPix(array $dados): array
    {
        $titulo           = trim($dados['titulo'] ?? '');
        $empresa          = trim($dados['empresa'] ?? '');
        $emailRecrutador = trim($dados['email_recrutador'] ?? '');
        $localizacao      = trim($dados['localizacao'] ?? 'Brasil');
        $modeloTrabalho   = trim($dados['modelo_trabalho'] ?? 'Remoto');
        $urlVaga          = trim($dados['url_vaga'] ?? '');
        $descricao        = trim($dados['descricao'] ?? '');
        $area             = trim($dados['area'] ?? 'geral');

        if (empty($titulo) || empty($empresa) || empty($emailRecrutador) || empty($descricao)) {
            throw new Exception("Por favor, preencha todos os campos obrigatórios (Título, Empresa, E-mail e Descrição).");
        }

        if (!filter_var($emailRecrutador, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Endereço de e-mail do recrutador é inválido.");
        }

        $vagaIdExterno = 'mw_prem_' . uniqid();
        $magicToken = bin2hex(random_bytes(24));
        $resumo = mb_substr(strip_tags($descricao), 0, 250) . '...';

        $this->pdo->beginTransaction();
        try {
            // Insert vaga em status inativa até que o pagamento Pix seja confirmado
            $stmt = $this->pdo->prepare("INSERT INTO vagas (
                vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, 
                url_vaga, descricao, resumo, status, is_premium, 
                email_recrutador, magic_token, status_pagamento, origem, area
            ) VALUES (
                :vaga_id_externo, :titulo, :empresa, :localizacao, :modelo_trabalho, 
                :url_vaga, :descricao, :resumo, 'inativa', 1, 
                :email_recrutador, :magic_token, 'pendente', 'nacional', :area
            )");

            $stmt->execute([
                ':vaga_id_externo' => $vagaIdExterno,
                ':titulo'          => $titulo,
                ':empresa'         => $empresa,
                ':localizacao'     => $localizacao,
                ':modelo_trabalho' => $modeloTrabalho,
                ':url_vaga'        => $urlVaga,
                ':descricao'       => $descricao,
                ':resumo'          => $resumo,
                ':email_recrutador'=> $emailRecrutador,
                ':magic_token'     => $magicToken,
                ':area'            => $area
            ]);

            $vagaId  = (int)$this->pdo->lastInsertId();

            $cpfCnpj = trim($dados['cpf_cnpj'] ?? ($dados['tax_id'] ?? ''));

            // Generate Pix payment via Efibank API
            $pixData = $this->efibankService->criarCobrancaPix($vagaId, $titulo, $empresa, $emailRecrutador, $cpfCnpj);

            // Update vaga with pagbank_order_id
            $upStmt = $this->pdo->prepare("UPDATE vagas SET pagbank_order_id = :order_id WHERE id = :id");
            $upStmt->execute([':order_id' => $pixData['order_id'], ':id' => $vagaId]);

            // Insert into pedidos_pix
            $pedStmt = $this->pdo->prepare("INSERT INTO pedidos_pix (
                vaga_id, pagbank_order_id, reference_id, valor, qr_code_text, 
                qr_code_image, email_recrutador, status, expiration_date
            ) VALUES (
                :vaga_id, :order_id, :ref_id, :valor, :qr_text, 
                :qr_img, :email, 'PENDING', :exp_date
            )");

            $pedStmt->execute([
                ':vaga_id'   => $vagaId,
                ':order_id'  => $pixData['order_id'],
                ':ref_id'    => $pixData['reference_id'],
                ':valor'     => $pixData['valor'],
                ':qr_text'   => $pixData['qr_code_text'],
                ':qr_img'    => $pixData['qr_code_image'],
                ':email'     => $emailRecrutador,
                ':exp_date'  => $pixData['expiration_date']
            ]);

            $this->pdo->commit();

            $serverName = $_SERVER['HTTP_HOST'] ?? 'mondywork.com.br';
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            $scheme = $isHttps ? 'https' : 'http';
            $magicUrl = "{$scheme}://{$serverName}/editar-vaga.php?token={$magicToken}";

            return array_merge($pixData, [
                'vaga_id'     => $vagaId,
                'magic_token' => $magicToken,
                'magic_url'   => $magicUrl
            ]);
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Confirmar pagamento de vaga e ativar destaque por 30 dias
     */
    public function ativarVagaPagamentoConfirmado(int $vagaId, string $orderId): bool
    {
        $stmt = $this->pdo->prepare("SELECT id, status_pagamento, email_recrutador, magic_token, titulo, empresa FROM vagas WHERE id = :id OR pagbank_order_id = :order_id LIMIT 1");
        $stmt->execute([':id' => $vagaId, ':order_id' => $orderId]);
        $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vaga) {
            return false;
        }

        if ($vaga['status_pagamento'] === 'pago') {
            return true; // já ativada previamente
        }

        // Ativa vaga por 30 dias
        $up = $this->pdo->prepare("UPDATE vagas SET 
            status = 'ativa', 
            is_premium = 1, 
            status_pagamento = 'pago', 
            publicado_em = NOW(), 
            destaque_ate = DATE_ADD(NOW(), INTERVAL 30 DAY) 
            WHERE id = :id");
        $up->execute([':id' => $vaga['id']]);

        // Atualiza pedidos_pix
        $upPed = $this->pdo->prepare("UPDATE pedidos_pix SET status = 'PAID' WHERE vaga_id = :id OR pagbank_order_id = :order_id");
        $upPed->execute([':id' => $vaga['id'], ':order_id' => $orderId]);

        // Dispara e-mail com Magic Link
        $this->enviarEmailMagicLink($vaga['email_recrutador'], $vaga['titulo'], $vaga['empresa'], $vaga['magic_token']);

        return true;
    }

    /**
     * Verificar status do pagamento de uma vaga
     */
    public function verificarStatusPagamento(int $vagaId): array
    {
        $stmt = $this->pdo->prepare("SELECT id, status, is_premium, status_pagamento, pagbank_order_id, magic_token FROM vagas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $vagaId]);
        $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vaga) {
            return ['status' => 'NOT_FOUND'];
        }

        if ($vaga['status_pagamento'] === 'pago') {
            $serverName = $_SERVER['HTTP_HOST'] ?? 'mondywork.com.br';
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            return [
                'status'     => 'PAID',
                'is_premium' => true,
                'magic_url'  => "{$scheme}://{$serverName}/editar-vaga.php?token=" . $vaga['magic_token']
            ];
        }

        // Se for pedido mock em ambiente local/testes, permite checar
        if ($vaga['pagbank_order_id'] && strpos($vaga['pagbank_order_id'], 'MOCK_ORDER_') === 0) {
            return ['status' => 'PENDING'];
        }

        // Consulta API da Efibank
        if (!empty($vaga['pagbank_order_id'])) {
            try {
                $cobranca = $this->efibankService->consultarCobranca($vaga['pagbank_order_id']);
                $status = strtoupper($cobranca['status'] ?? 'ATIVA');
                $temPix = !empty($cobranca['pix']) && is_array($cobranca['pix']);
                
                if ($status === 'CONCLUIDA' || $status === 'PAID' || $status === 'APPROVED' || $temPix) {
                    $this->ativarVagaPagamentoConfirmado((int)$vaga['id'], $vaga['pagbank_order_id']);
                    $serverName = $_SERVER['HTTP_HOST'] ?? 'mondywork.com';
                    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    return [
                        'status'     => 'PAID',
                        'is_premium' => true,
                        'magic_url'  => "{$scheme}://{$serverName}/editar-vaga.php?token=" . $vaga['magic_token']
                    ];
                }
            } catch (Exception $e) {
                // Silently return pending if request fails
            }
        }

        return ['status' => 'PENDING'];
    }

    /**
     * Envia e-mail nativo PHP com o Magic Link para a empresa
     */
    private function enviarEmailMagicLink(string $email, string $titulo, string $empresa, string $magicToken): void
    {
        if (empty($email)) return;

        $serverName = $_SERVER['HTTP_HOST'] ?? 'mondywork.com.br';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $magicUrl = "{$scheme}://{$serverName}/editar-vaga.php?token={$magicToken}";

        $subject = "🚀 Vaga Ativada com Sucesso - Mondy Work (" . $titulo . ")";
        $message = "
        <html>
        <head>
          <title>Vaga Premium Ativada!</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; background-color: #f9f9f9; padding: 20px;'>
          <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; border: 1px solid #eee;'>
            <h2 style='color: #6b21a8; margin-top: 0;'>Sua vaga está AO VIVO e em Destaque! 🚀</h2>
            <p>Olá, representante da <strong>" . htmlspecialchars($empresa) . "</strong>!</p>
            <p>Confirmamos o pagamento da sua vaga <strong>" . htmlspecialchars($titulo) . "</strong> no Mondy Work. O anúncio já está destacado na plataforma pelo período de 30 dias.</p>
            
            <div style='background-color: #f3e8ff; border: 1px solid #d8b4fe; padding: 20px; border-radius: 8px; margin: 25px 0;'>
              <p style='margin: 0 0 10px 0; font-weight: bold; color: #581c87;'>🔗 Seu Magic Link de Gerenciamento:</p>
              <p style='margin: 0;'>Guarde este link para editar as informações ou encerrar a vaga quando desejar:</p>
              <p style='margin-top: 15px;'><a href='" . $magicUrl . "' style='background: #7e22ce; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 6px; display: inline-block; font-weight: bold;'>Gerenciar Vaga Agora</a></p>
            </div>
            
            <p style='font-size: 12px; color: #777;'>Ou copie o link: " . $magicUrl . "</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
            <p style='font-size: 13px; color: #999;'>Mondy Work - Plataforma de Vagas & Carreiras</p>
          </div>
        </body>
        </html>
        ";

        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
        $headers[] = 'From: Mondy Work <contato@' . $serverName . '>';

        @mail($email, $subject, $message, implode("\r\n", $headers));
    }
}
