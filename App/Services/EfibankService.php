<?php

namespace App\Services;

use App\Core\Config;
use Exception;

class EfibankService
{
    private string $clientId;
    private string $clientSecret;
    private string $chavePix;
    private string $certificatePath;
    private string $env;
    private string $baseUrl;
    private float $precoVaga;
    private int $validadePixMinutos;

    public function __construct()
    {
        $config = Config::get('efibank', []);
        $this->clientId        = trim($config['client_id'] ?? '');
        $this->clientSecret    = trim($config['client_secret'] ?? '');
        $this->chavePix        = trim($config['chave_pix'] ?? '');
        $this->certificatePath = trim($config['certificate_path'] ?? '');
        $this->env             = $config['env'] ?? 'sandbox';
        $this->precoVaga       = (float)($config['preco_vaga_premium'] ?? 49.90);
        $this->validadePixMinutos = (int)($config['validade_pix_minutos'] ?? 30);

        if ($this->env === 'production') {
            $this->baseUrl = 'https://pix.api.efipay.com.br';
        } else {
            $this->baseUrl = 'https://pix-h.api.efipay.com.br';
        }
    }

    /**
     * Obter Token de Acesso OAuth2 da Efibank
     */
    public function getAccessToken(): string
    {
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception("Credenciais da Efibank (client_id / client_secret) não configuradas.");
        }

        if (!file_exists($this->certificatePath)) {
            throw new Exception("Certificado da Efibank não encontrado em: {$this->certificatePath}");
        }

        $url = $this->baseUrl . '/oauth/token';
        $basicAuth = base64_encode("{$this->clientId}:{$this->clientSecret}");

        $headers = [
            'Authorization: Basic ' . $basicAuth,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $payload = json_encode(['grant_type' => 'client_credentials']);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->certificatePath);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            throw new Exception("Erro cURL ao autenticar na Efibank: " . $curlError);
        }

        $decoded = json_decode($responseBody, true) ?? [];

        if ($httpCode >= 400 || empty($decoded['access_token'])) {
            $msg = $decoded['error_description'] ?? ($decoded['error'] ?? "HTTP Code {$httpCode}");
            throw new Exception("Erro de Autenticação Efibank ({$httpCode}): {$msg}");
        }

        return $decoded['access_token'];
    }

    /**
     * Criar ordem de pagamento Pix na Efibank (POST /v2/cob)
     */
    public function criarCobrancaPix(int $vagaId, string $tituloVaga, string $empresa, string $emailRecrutador, string $cpfCnpj = ''): array
    {
        if (empty($this->clientId) || strpos($this->clientId, 'YOUR_') === 0) {
            return $this->gerarPixSimuladoDev($vagaId);
        }

        $token = $this->getAccessToken();

        $payload = [
            'calendario' => [
                'expiracao' => $this->validadePixMinutos * 60
            ],
            'valor' => [
                'original' => sprintf('%.2f', $this->precoVaga)
            ],
            'chave' => $this->chavePix,
            'solicitacaoPagador' => mb_substr("Vaga Premium: {$tituloVaga}", 0, 140)
        ];

        // Se CPF ou CNPJ for informado e válido, adiciona devedor
        $cpfCnpjClean = preg_replace('/\D/', '', $cpfCnpj);
        $customerName = trim($empresa);
        if (empty($customerName)) {
            $customerName = 'Recrutador MondyWork';
        }

        if (strlen($cpfCnpjClean) === 11 && $this->validarCpf($cpfCnpjClean)) {
            $payload['devedor'] = [
                'cpf'  => $cpfCnpjClean,
                'nome' => mb_substr($customerName, 0, 200)
            ];
        } elseif (strlen($cpfCnpjClean) === 14 && $this->validarCnpj($cpfCnpjClean)) {
            $payload['devedor'] = [
                'cnpj' => $cpfCnpjClean,
                'nome' => mb_substr($customerName, 0, 200)
            ];
        }

        $response = $this->request('POST', '/v2/cob', $payload, $token);

        if (empty($response['txid']) || empty($response['pixCopiaECola'])) {
            $err = json_encode($response);
            throw new Exception("Falha ao gerar cobrança Pix na Efibank: {$err}");
        }

        $txid = $response['txid'];
        $pixCopiaECola = $response['pixCopiaECola'];
        $locId = $response['loc']['id'] ?? null;
        $qrCodeImage = '';

        // Buscar a imagem do QR Code em Base64 se houver loc.id
        if ($locId) {
            try {
                $qrRes = $this->request('GET', "/v2/loc/{$locId}/qrcode", null, $token);
                if (!empty($qrRes['imagemQrcode'])) {
                    $qrCodeImage = $qrRes['imagemQrcode']; // data:image/png;base64,...
                }
            } catch (Exception $e) {
                // Se falhar o QR Code base64, gera via API publica usando o pixCopiaECola
                $qrCodeImage = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($pixCopiaECola);
            }
        }

        return [
            'order_id'        => $txid,
            'reference_id'   => 'VAGA_' . $vagaId . '_' . time(),
            'valor'           => $this->precoVaga,
            'qr_code_text'    => $pixCopiaECola,
            'qr_code_image'   => $qrCodeImage,
            'expiration_date' => date('Y-m-d H:i:s', strtotime("+{$this->validadePixMinutos} minutes")),
            'status'          => 'PENDING'
        ];
    }

    /**
     * Consultar status de uma cobrança Pix na Efibank (GET /v2/cob/{txid})
     */
    public function consultarCobranca(string $txid): array
    {
        if (empty($this->clientId) || strpos($this->clientId, 'YOUR_') === 0) {
            return ['status' => 'PENDING'];
        }

        $token = $this->getAccessToken();
        return $this->request('GET', "/v2/cob/{$txid}", null, $token);
    }

    /**
     * Requisição cURL genérica para a API Pix Efibank com Bearer token e Certificado PEM
     */
    private function request(string $method, string $endpoint, ?array $payload = null, string $token = ''): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSLCERT, $this->certificatePath);
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false) {
            throw new Exception("Erro cURL Efibank ({$endpoint}): " . $curlError);
        }

        $decoded = json_decode($responseBody, true) ?? [];

        if ($httpCode >= 400) {
            $msg = $decoded['mensagem'] ?? ($decoded['nome'] ?? "HTTP Code {$httpCode}");
            throw new Exception("Erro API Efibank ({$httpCode}): {$msg}");
        }

        return $decoded;
    }

    /**
     * Simula Pix em ambiente local para testes se necessário
     */
    private function gerarPixSimuladoDev(int $vagaId): array
    {
        $mockTxId = 'MOCK_EFI_' . $vagaId . '_' . rand(1000, 9999);
        $mockPixCode = "00020101021226840014br.gov.bcb.pix0136mock-mondywork-{$vagaId}-pix-code520400005303986540549.905802BR5910MondyWork6009SAO_PAULO62070503***6304E8A2";

        return [
            'order_id'        => $mockTxId,
            'reference_id'   => 'VAGA_' . $vagaId . '_MOCK',
            'valor'           => $this->precoVaga,
            'qr_code_text'    => $mockPixCode,
            'qr_code_image'   => "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($mockPixCode),
            'expiration_date' => date('Y-m-d H:i:s', strtotime("+{$this->validadePixMinutos} minutes")),
            'status'          => 'PENDING'
        ];
    }

    /**
     * Valida algoritmo do CPF
     */
    private function validarCpf(string $cpf): bool
    {
        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int)$cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int)$cpf[$c] != $d) return false;
        }
        return true;
    }

    /**
     * Valida algoritmo do CNPJ
     */
    private function validarCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) return false;
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += (int)$cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ((int)$cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) return false;
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += (int)$cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return (int)$cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
}
