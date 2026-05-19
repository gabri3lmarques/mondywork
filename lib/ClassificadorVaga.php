<?php

/**
 * Mapeamento de cabeçalhos de seção para macro-áreas
 */
function mapaAreas(): array
{
    return [
        'dev'      => ['dev', 'engenharia', 'tecnologia', 'linguagens', 'frameworks', 'ferramentas'],
        'design'   => ['design', 'criativo'],
        'marketing' => ['marketing', 'comunicação', 'comunicacao'],
        'digital'  => ['digital', 'produto', 'dados', 'gestão', 'gestao'],
    ];
}

function identificarArea(string $nomeSecao): ?string
{
    $nome = mb_strtolower(trim($nomeSecao));
    foreach (mapaAreas() as $area => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($nome, $kw) !== false) {
                return $area;
            }
        }
    }
    return null;
}

function carregarTermos(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $arquivo = __DIR__ . '/../novos_termos.md';
    if (!file_exists($arquivo)) {
        $cache = ['inclusao' => [], 'exclusao' => []];
        return $cache;
    }

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        $cache = ['inclusao' => [], 'exclusao' => []];
        return $cache;
    }

    $modo = null; // 'inclusao' | 'exclusao'
    $secaoAtual = null;
    $areaAtual = null;
    $termos = ['inclusao' => [], 'exclusao' => []];

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if (preg_match('/^(INCLUSÃO|INCLUSAO)/i', $linha)) {
            $modo = 'inclusao';
            continue;
        }

        if (preg_match('/^(EXCLUSÃO|EXCLUSAO)/i', $linha)) {
            $modo = 'exclusao';
            $secaoAtual = null;
            $areaAtual = null;
            continue;
        }

        if (str_starts_with($linha, '##')) {
            $nomeSecao = trim(ltrim($linha, '# '));
            $nomeSecao = preg_replace('/\*\*/', '', $nomeSecao);
            $nomeSecao = preg_replace('/\s*\(.*\)/', '', $nomeSecao);

            if ($modo === 'inclusao') {
                $areaAtual = identificarArea($nomeSecao) ?? 'geral';
                $secaoAtual = 'inclusao';
            } elseif ($modo === 'exclusao') {
                $secaoAtual = 'exclusao';
                $areaAtual = null;
            }
            continue;
        }

        if (str_starts_with($linha, '💡')) {
            $modo = null;
            continue;
        }

        if (empty($linha) || str_starts_with($linha, '#') || str_starts_with($linha, '*') || str_starts_with($linha, '(') || str_starts_with($linha, '//') || str_starts_with($linha, '\\') || preg_match('/^(Obs|ex:)/i', $linha)) {
            continue;
        }

        if ($modo === null) {
            continue;
        }

        $linha = str_replace('\\#', '#', $linha);

        preg_match_all('/"([^"]+)"|(\S+)/', $linha, $matches);

        foreach ($matches[0] as $match) {
            $termo = mb_strtolower(trim($match, '"'));
            $termo = trim($termo);
            if ($termo === '' || !preg_match('/^[a-zà-ü0-9][a-zà-ü0-9\s.\/-]*$/u', $termo)) {
                continue;
            }
            $genericos = ['pleno', 'senior', 'junior', 'jr', 'sr', 'pl', 'especialista', 'entry-level', 'mid-level', 'trainee', 'estágio', 'estagio', 'internship', 'intern', 'estagiário', 'estagiario', 'go', 'po', 'vp', 'pr'];
            if (mb_strlen($termo) < 3 && !in_array($termo, $genericos)) {
                $genericos[] = $termo;
            } // termos de 2-3 letras com muitos fp
            if ($secaoAtual === 'inclusao' && $areaAtual) {
                if (in_array($termo, $genericos)) {
                    $termos['inclusao']['_genericos'][] = $termo;
                    continue;
                }
                $termos['inclusao'][$areaAtual][] = $termo;
                $termos['inclusao']['_todas'][] = $termo;
            } elseif ($secaoAtual === 'exclusao') {
                $termos['exclusao'][] = $termo;
            }
        }
    }

    if (isset($termos['inclusao']['_todas'])) {
        $termos['inclusao']['_todas'] = array_unique($termos['inclusao']['_todas']);
    }
    $termos['exclusao'] = array_unique($termos['exclusao']);

    $cache = $termos;
    return $termos;
}

function removerAcentos(string $str): string
{
    $comAcentos = 'áàãâäéèêëíìîïóòõôöúùûüçñÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÑ';
    $semAcentos = 'aaaaaeeeeiiiiooooouuuucnAAAAAEEEEIIIIOOOOOUUUUCN';
    return strtr($str, $comAcentos, $semAcentos);
}

function matcharTermo(string $texto, string $termo): bool
{
    $delim = '[\s,.\/;:!?()\[\]{}"\'\-]';
    $pattern = '/(?:^|' . $delim . ')' . preg_quote(removerAcentos(mb_strtolower($termo)), '/') . '(?=' . $delim . '|$)/iu';
    return (bool) preg_match($pattern, removerAcentos(mb_strtolower($texto)));
}

function temMatchInclusao(string $texto, array $termos, bool $ignorarGenericos = false): bool
{
    foreach ($termos['inclusao'] as $area => $termosArea) {
        if ($area === '_todas' || $area === '_genericos') continue;
        foreach ($termosArea as $termo) {
            if (matcharTermo($texto, $termo)) {
                return true;
            }
        }
    }
    return false;
}

function classificarVaga(string $titulo, string $descricao = ''): array
{
    $termos = carregarTermos();

    if (empty($termos['inclusao']) || !isset($termos['inclusao']['_todas'])) {
        return ['aprovada' => true, 'area' => null, 'motivo' => 'sem_termos'];
    }

    $textoCompleto = mb_strtolower($titulo . ' ' . strip_tags($descricao));
    $textoTitulo = mb_strtolower($titulo);

    foreach ($termos['exclusao'] as $termo) {
        if (matcharTermo($textoTitulo, $termo)) {
            if (!temMatchInclusao($textoCompleto, $termos)) {
                return ['aprovada' => false, 'area' => null, 'motivo' => 'exclusao'];
            }
        }
    }

    foreach ($termos['inclusao'] as $area => $termosArea) {
        if ($area === '_todas' || $area === '_genericos') continue;
        foreach ($termosArea as $termo) {
            if (matcharTermo($textoCompleto, $termo)) {
                return ['aprovada' => true, 'area' => $area, 'motivo' => 'match_' . $area];
            }
        }
    }

    return ['aprovada' => false, 'area' => null, 'motivo' => 'sem_match'];
}
