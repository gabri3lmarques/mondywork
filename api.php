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

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $campos = "vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, resumo, descricao, DATE_FORMAT(publicado_em, '%d/%m/%Y') as publicado_em, area";

    if ($vagaId !== '') {
        $stmt = $pdo->prepare("SELECT $campos FROM vagas WHERE vaga_id_externo = :id AND status = 'ativa' LIMIT 1");
        $stmt->execute([':id' => $vagaId]);
        $vaga = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($vaga);
        return;
    }

    $areaCondicao = $area !== '' ? " AND area = " . $pdo->quote($area) : "";

    if ($q !== '') {
        $searchTerm = $pdo->quote($q);
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao . " AND MATCH(titulo, empresa, localizacao, descricao, resumo) AGAINST($searchTerm IN BOOLEAN MODE)");
        $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
        $totalStmt->execute();
        $total = (int)$totalStmt->fetchColumn();

        $sql = "SELECT $campos,
                MATCH(titulo, empresa, localizacao, descricao, resumo) AGAINST($searchTerm) AS score
                FROM vagas
                WHERE status = 'ativa'
                AND origem = :origem
                $areaCondicao
                AND MATCH(titulo, empresa, localizacao, descricao, resumo) AGAINST($searchTerm IN BOOLEAN MODE)
                ORDER BY vagas.publicado_em DESC, score DESC
                LIMIT :limit OFFSET :offset";
    } else {
        $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem" . $areaCondicao);
        $totalStmt->bindValue(':origem', $origem, PDO::PARAM_STR);
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
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'query'     => $q,
        'data'      => $vagas,
        'page'      => $page,
        'limit'     => $limit,
        'total'     => $total,
        'area'      => $area,
        'has_more'  => ($offset + $limit) < $total,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar vagas', 'detail' => $e->getMessage()]);
}
