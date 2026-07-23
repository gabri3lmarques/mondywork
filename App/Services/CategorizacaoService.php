<?php

namespace App\Services;

class CategorizacaoService
{
    private static ?array $dictionary = null;

    public static function getDictionary(): array
    {
        if (self::$dictionary === null) {
            $path = dirname(__DIR__, 2) . '/categorias.php';
            if (file_exists($path)) {
                global $categorias_mondywork;
                require_once $path;
                self::$dictionary = $categorias_mondywork ?? [];
            } else {
                self::$dictionary = [];
            }
        }
        return self::$dictionary;
    }

    public static function classificar(string $titulo): array
    {
        $dict = self::getDictionary();
        $tituloClean = mb_strtolower(self::removerAcentos($titulo));
        $tags = [];

        foreach ($dict as $categoria => $termos) {
            foreach ($termos as $termo) {
                $raw = $termo;
                $termoClean = mb_strtolower(self::removerAcentos(trim($termo, '"')));

                if (str_starts_with($raw, '"')) {
                    if (str_contains($tituloClean, $termoClean)) {
                        $tags[] = $categoria;
                        break;
                    }
                } else {
                    if (preg_match('/\b' . preg_quote($termoClean, '/') . '\b/u', $tituloClean)) {
                        $tags[] = $categoria;
                        break;
                    }
                }
            }
        }

        if (empty($tags)) {
            $tags[] = 'Sem Categoria';
        }

        return array_values(array_unique($tags));
    }

    public static function categoriaSlug(string $nome): string
    {
        $map = [
            'Desenvolvimento' => 'desenvolvimento',
            'Desenvolvedor Mobile' => 'desenvolvedor-mobile',
            'Engenharia' => 'engenharia',
            'Dados' => 'dados',
            'IA' => 'ia',
            'Design' => 'design',
            'Marketing Digital' => 'marketing-digital',
            'Conteúdo' => 'conteudo',
            'Produto' => 'produto',
            'Ágil' => 'agil',
            'Gestão Projetos' => 'gestao-projetos',
            'Comercial' => 'comercial',
            'Customer Success' => 'customer-success',
            'Suporte Técnico' => 'suporte-tecnico',
            'QA/Testes' => 'qa-testes',
            'Infra/DevOps' => 'infra-devops',
            'Financeiro' => 'financeiro',
            'RH/Gente' => 'rh-gente',
            'Administração' => 'administracao',
            'Jurídico' => 'juridico',
            'Segurança da Informação' => 'seguranca-da-informacao',
            'Business Intelligence' => 'business-intelligence',
            'E-commerce' => 'e-commerce',
            'Operações' => 'operacoes',
            'Saúde' => 'saude',
            'Educação' => 'educacao',
            'Logística' => 'logistica',
        ];

        return $map[$nome] ?? self::slugify($nome);
    }

    public static function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $map = ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n'];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        return trim($text, '-');
    }

    private static function removerAcentos(string $str): string
    {
        $map = ['á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n'];
        return strtr(mb_strtolower($str), $map);
    }
}
