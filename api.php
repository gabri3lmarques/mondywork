<?php
header('Content-Type: application/json; charset=utf-8');

$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;

$page  = isset($_GET['page'])  ? max(1, (int)$_GET['page'])  : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$origem = isset($_GET['origem']) ? trim($_GET['origem']) : 'nacional';
$area = isset($_GET['area']) ? trim($_GET['area']) : '';
$vagaId = isset($_GET['vaga_id']) ? trim($_GET['vaga_id']) : '';
$modo = isset($_GET['modo']) && $_GET['modo'] === 'descricao' ? 'descricao' : 'titulo';

function removerAcentos($str) {
    $acentos = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
        'ç' => 'c', 'ð' => 'd',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y', 'þ' => 'b', 'ß' => 'ss',
        'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
        'Ç' => 'C', 'Ð' => 'D',
        'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ñ' => 'N',
        'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
        'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ý' => 'Y', 'Ÿ' => 'Y', 'Þ' => 'B',
    ];
    return strtr($str, $acentos);
}

function corrigirQueryFuzzy($query, $pdo) {
    $palavras = preg_split('/\s+/', trim($query));
    $palavras = array_values(array_filter($palavras, function($p) { return $p !== ''; }));

    if (count($palavras) <= 1) return [$query, false];

    $stmt = $pdo->query("SELECT DISTINCT titulo FROM vagas WHERE status = 'ativa' AND titulo IS NOT NULL AND titulo != ''");
    $titulos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $dicionario = [];
    foreach ($titulos as $titulo) {
        foreach (preg_split('/\s+/', $titulo) as $palavra) {
            $p = trim(mb_strtolower($palavra));
            $p = removerAcentos($p);
            $p = preg_replace('/[^a-z0-9]/', '', $p);
            if (mb_strlen($p) > 1) {
                $dicionario[$p] = true;
            }
        }
    }

    if (empty($dicionario)) return [$query, false];

    $stopwords = ['de', 'da', 'do', 'das', 'dos', 'em', 'no', 'na', 'nos', 'nas', 'com', 'para', 'por', 'e', 'a', 'ao', 'aos', 'o', 'um', 'uma', 'é', 'se', 'sua', 'seu', 'que', 'mais', 'mas', 'como'];

    $houveCorrecao = false;
    $palavrasCorrigidas = [];

    foreach ($palavras as $palavra) {
        $lower = trim(mb_strtolower($palavra));
        $lowerNorm = removerAcentos($lower);
        $lowerNorm = preg_replace('/[^a-z0-9]/', '', $lowerNorm);

        if (mb_strlen($lowerNorm) <= 1 || in_array($lowerNorm, $stopwords)) {
            $palavrasCorrigidas[] = $palavra;
            continue;
        }

        if (isset($dicionario[$lowerNorm])) {
            $palavrasCorrigidas[] = $palavra;
            continue;
        }

        $melhorDistancia = PHP_INT_MAX;
        $melhorPalavra = null;
        foreach ($dicionario as $palavraDict => $_) {
            $dist = levenshtein($lowerNorm, $palavraDict);
            $maxDist = mb_strlen($lowerNorm) <= 4 ? 1 : (mb_strlen($lowerNorm) <= 7 ? 2 : 3);
            if ($dist < $melhorDistancia && $dist <= $maxDist) {
                $melhorDistancia = $dist;
                $melhorPalavra = $palavraDict;
            }
        }

        if ($melhorPalavra !== null) {
            $palavrasCorrigidas[] = $melhorPalavra;
            $houveCorrecao = true;
        } else {
            $palavrasCorrigidas[] = $palavra;
        }
    }

    return [implode(' ', $palavrasCorrigidas), $houveCorrecao];
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/lib/Database.php';
    setupSchema($pdo);

    $campos = "vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, resumo, descricao, DATE_FORMAT(publicado_em, '%d/%m/%Y') as publicado_em, area";

    if ($vagaId !== '') {
        $stmt = $pdo->prepare("SELECT $campos FROM vagas WHERE vaga_id_externo = :id AND status = 'ativa' LIMIT 1");
        $stmt->execute([':id' => $vagaId]);
        $vaga = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($vaga);
        return;
    }

    $areaCondicao = '';
    $areaParams = [];
    if ($area !== '') {
        $areaCondicao = ' AND area = :area';
        $areaParams[':area'] = $area;
    }

    $queryCorrigida = null;

    // MySQL full-text ignora palavras < 3 caracteres (innodb_ft_min_token_size)
    $shortTerms = false;
    if ($q !== '') {
        $termos = preg_split('/\s+/', trim($q));
        foreach ($termos as $t) {
            $t = trim($t);
            if ($t !== '' && mb_strlen($t) < 3) {
                $shortTerms = true;
                break;
            }
        }
    }

    if ($q !== '') {
        if ($modo === 'descricao') {
            $likeQ = '%' . $q . '%';

            $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao . " AND (descricao LIKE :like_q OR resumo LIKE :like_q)");
            $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
            foreach ($areaParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
            $totalStmt->bindValue(':like_q', $likeQ, PDO::PARAM_STR);
            $totalStmt->execute();
            $total = (int)$totalStmt->fetchColumn();

            $sql = "SELECT $campos
                    FROM vagas
                    WHERE status = 'ativa'
                    AND origem = :origem
                    $areaCondicao
                    AND (descricao LIKE :like_q OR resumo LIKE :like_q)
                    ORDER BY vagas.publicado_em DESC
                    LIMIT :limit OFFSET :offset";
        } elseif ($shortTerms) {
            $termos = preg_split('/\s+/', trim($q));
            $conds = [];
            $wordParams = [];
            foreach ($termos as $i => $t) {
                $t = trim($t);
                if ($t === '') continue;
                $pn = ":w_{$i}";
                $conds[] = "CONCAT(' ',COALESCE(titulo,''),' ') LIKE CONCAT('% ',{$pn},' %')";
                $wordParams[$pn] = $t;
            }
            $wordCond = implode(' AND ', $conds);

            $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao . " AND {$wordCond}");
            $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
            foreach ($areaParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
            foreach ($wordParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
            $totalStmt->execute();
            $total = (int)$totalStmt->fetchColumn();

            $sql = "SELECT $campos
                    FROM vagas
                    WHERE status = 'ativa'
                    AND origem = :origem
                    $areaCondicao
                    AND {$wordCond}
                    ORDER BY vagas.publicado_em DESC
                    LIMIT :limit OFFSET :offset";
        } else {
            $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao . " AND MATCH(titulo) AGAINST(:search_q IN BOOLEAN MODE)");
            $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
            foreach ($areaParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
            $totalStmt->bindValue(':search_q', $q, PDO::PARAM_STR);
            $totalStmt->execute();
            $total = (int)$totalStmt->fetchColumn();

            if ($total === 0) {
                [$corrigida, $houveCorrecao] = corrigirQueryFuzzy($q, $pdo);
                if ($houveCorrecao) {
                    $queryCorrigida = $corrigida;

                    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao . " AND MATCH(titulo) AGAINST(:search_q IN BOOLEAN MODE)");
                    $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
                    foreach ($areaParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
                    $totalStmt->bindValue(':search_q', $corrigida, PDO::PARAM_STR);
                    $totalStmt->execute();
                    $total = (int)$totalStmt->fetchColumn();
                }
            }

            $searchQ = $queryCorrigida ?? $q;

            $sql = "SELECT $campos,
                    MATCH(titulo) AGAINST(:search_q) AS score
                    FROM vagas
                    WHERE status = 'ativa'
                    AND origem = :origem
                    $areaCondicao
                    AND MATCH(titulo) AGAINST(:search_q IN BOOLEAN MODE)
                    ORDER BY score DESC, vagas.publicado_em DESC
                    LIMIT :limit OFFSET :offset";
        }
    } else {
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao);
        $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
        foreach ($areaParams as $k => $v) $totalStmt->bindValue($k, $v, PDO::PARAM_STR);
        $totalStmt->execute();
        $total = (int)$totalStmt->fetchColumn();

        $sql = "SELECT $campos
                FROM vagas
                WHERE status = 'ativa'
                AND origem = :origem
                $areaCondicao
                ORDER BY vagas.publicado_em DESC, data_coleta DESC
                LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':origem', $origem, PDO::PARAM_STR);
    foreach ($areaParams as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    if ($q !== '') {
        if ($modo === 'descricao') {
            $stmt->bindValue(':like_q', $likeQ, PDO::PARAM_STR);
        } elseif ($shortTerms) {
            foreach ($wordParams as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
        } else {
            $stmt->bindValue(':search_q', $searchQ, PDO::PARAM_STR);
        }
    }
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'query'           => $q,
        'query_corrigida' => $queryCorrigida,
        'data'            => $vagas,
        'page'            => $page,
        'limit'           => $limit,
        'total'           => $total,
        'area'            => $area,
        'modo'            => $modo,
        'has_more'        => ($offset + $limit) < $total,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar vagas', 'detail' => $e->getMessage()]);
}
