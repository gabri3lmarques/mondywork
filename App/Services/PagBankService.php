<?php

namespace App\Services;

use App\Core\Config;
use Exception;

class PagBankService
{
    private string $token;
    private string $env;
    private string $baseUrl;
    private float $precoVaga;
    private int $validadePixMinutos;

    public function __construct()
    {
        $pagbankConfig = Config::get('pagbank', []);
        $this->token = $pagbankConfig['token'] ?? '';
        $this->env = $pagbankConfig['env'] ?? 'sandbox';
        $this->precoVaga = (float)($pagbankConfig['preco_vaga_premium'] ?? 49.90);
        $this->validadePixMinutos = (int)($pagbankConfig['validade_pix_minutos'] ?? 30);

        if ($this->env === 'production') {
            $this->baseUrl = 'https://api.pagseguro.com';
        } else {
            $this->baseUrl = 'https://sandbox.api.pagseguro.com';
        }
    }

    /**
     * Criar ordem de pagamento Pix no PagBank
     */
    public function criarCobrancaPix(int $vagaId, string $tituloVaga, string $empresa, string $emailRecrutador): array
    {
        if (empty($this->token) || $this->token === 'YOUR_PAGBANK_SANDBOX_TOKEN') {
            // Gera um payload simulado em ambiente local para testes visuais sem bloquear dev
            return $this->gerarPixSimuladoDev($vagaId, $emailRecrutador);
        }

        $referenceId = 'VAGA_' . $vagaId . '_' . time();
        $expirationDate = date('Y-m-d\TH:i:sP', strtotime("+{$this->validadePixMinutos} minutes"));
        $amountCents = (int)round($this->precoVaga * 100);

        $serverName = $_SERVER['HTTP_HOST'] ?? 'mondywork.com.br';
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $notificationUrl = "{$scheme}://{$serverName}/api.php?action=pagbank_webhook";

        $payload = [
            'reference_id' => $referenceId,
            'customer' => [
                'name'  => mb_substr($empresa, 0, 30),
                'email' => $emailRecrutador,
            ],
            'items' => [
                [
                    'name'        => mb_substr("Anúncio Vaga Premium: {$tituloVaga}", 0, 100),
                    'quantity'    => 1,
                    'unit_amount' => $amountCents,
                ]
            ],
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $amountCents
                    ],
                    'expiration_date' => $expirationDate
                ]
            ],
            'notification_urls' => [
                $notificationUrl
            ]
        ];

        $response = $this->request('POST', '/orders', $payload);

        if (!isset($response['id']) || !isset($response['qr_codes'][0])) {
            $errorMsg = $response['error_messages'][0]['description'] ?? 'Erro desconhecido ao gerar Pix no PagBank.';
            throw new Exception("Falha ao comunicar com PagBank: " . $errorMsg);
        }

        $qrCode = $response['qr_codes'][0];
        $qrCodeText = $qrCode['text'] ?? '';
        $qrCodeImage = '';

        if (isset($qrCode['links'])) {
            foreach ($qrCode['links'] as $link) {
                if (($link['rel'] ?? '') === 'QRCODE.PNG') {
                    $qrCodeImage = $link['href'];
                    break;
                }
            }
        }

        return [
            'order_id'        => $response['id'],
            'reference_id'   => $referenceId,
            'valor'           => $this->precoVaga,
            'qr_code_text'    => $qrCodeText,
            'qr_code_image'   => $qrCodeImage,
            'expiration_date' => date('Y-m-d H:i:s', strtotime("+{$this->validadePixMinutos} minutes")),
            'status'          => 'PENDING'
        ];
    }

    /**
     * Consultar status de uma ordem no PagBank
     */
    public function consultarPedido(string $orderId): array
    {
        if (empty($this->token) || $this->token === 'YOUR_PAGBANK_SANDBOX_TOKEN') {
            return ['status' => 'PENDING'];
        }

        return $this->request('GET', "/orders/{$orderId}");
    }

    /**
     * Requisição HTTP cURL para a API PagBank
     */
    private function request(string $method, string $endpoint, ?array $payload = null): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            throw new Exception("Erro cURL PagBank: " . $curlError);
        }

        $decoded = json_decode($responseBody, true) ?? [];

        if ($httpCode >= 400) {
            $msg = $decoded['error_messages'][0]['description'] ?? "HTTP Code {$httpCode}";
            throw new Exception("Erro PagBank API ({$httpCode}): {$msg}");
        }

        return $decoded;
    }

    /**
     * Gera dados de Pix simulados em ambiente local de teste
     */
    private function gerarPixSimuladoDev(int $vagaId, string $email): array
    {
        $mockOrderId = 'MOCK_ORDER_' . $vagaId . '_' . rand(1000, 9999);
        $mockPixCode = "00020101021226840014br.gov.bcb.pix0136mock-mondywork-{$vagaId}-pix-code520400005303986540549.905802BR5910MondyWork6009SAO_PAULO62070503***6304E8A2";

        return [
            'order_id'        => $mockOrderId,
            'reference_id'   => 'VAGA_' . $vagaId . '_MOCK',
            'valor'           => $this->precoVaga,
            'qr_code_text'    => $mockPixCode,
            'qr_code_image'   => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($mockPixCode),
            'expiration_date' => date('Y-m-d H:i:s', strtotime("+{$this->validadePixMinutos} minutes")),
            'status'          => 'PENDING'
        ];
    }
}
