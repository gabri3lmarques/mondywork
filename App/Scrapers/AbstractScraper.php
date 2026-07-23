<?php

namespace App\Scrapers;

abstract class AbstractScraper implements ScraperInterface
{
    protected function makeCurlRequest(string $url, array $options = []): ?string
    {
        $ch = curl_init($url);
        $defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_USERAGENT      => 'Mondywork-Scraper/1.0',
        ];

        curl_setopt_array($ch, $options + $defaultOptions);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return null;
        }

        return $response;
    }

    protected function extrairResumo(string $html, int $maxChars = 280): string
    {
        $html  = preg_replace('/\s*style\s*=\s*["\'][^"\']*["\']\s*/i', ' ', $html);
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
}
