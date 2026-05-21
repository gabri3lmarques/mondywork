<?php
error_reporting(0);
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

function json_safe($data, $code = 200) {
    http_response_code($code);
    $out = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
    if ($out === false) {
        $out = json_encode(['error' => 'Erro interno de codificação'], JSON_UNESCAPED_UNICODE);
    }
    echo $out;
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_safe(['success' => false, 'code' => 'method_not_allowed'], 405);
    }

    $configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
    if (!file_exists($configFile)) {
        json_safe(['success' => false, 'code' => 'config_missing'], 500);
    }
    $config = require $configFile;

    $nome   = isset($_POST['nome'])   ? trim($_POST['nome'])   : '';
    $email  = isset($_POST['email'])  ? trim($_POST['email'])  : '';
    $area   = isset($_POST['area'])   ? trim($_POST['area'])   : null;
    $origem = isset($_POST['origem']) ? trim($_POST['origem']) : 'brasil';

    if ($nome === '' || $email === '') {
        json_safe(['success' => false, 'code' => 'missing_fields'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_safe(['success' => false, 'code' => 'invalid_email'], 400);
    }

    $areasValidas = ['dev', 'engenharia', 'dados', 'design', 'marketing', 'social-media', 'produto', 'agile', 'gestao', 'vendas', 'customer-success', 'suporte', 'qa', 'infra'];
    if ($area === null || $area === '' || !in_array($area, $areasValidas)) {
        json_safe(['success' => false, 'code' => 'invalid_area'], 400);
    }

    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    require_once __DIR__ . '/lib/Database.php';
    setupSchema($pdo);

    $stmt = $pdo->prepare("INSERT INTO newsletters (nome, email, area, origem) VALUES (:nome, :email, :area, :origem)");
    $stmt->execute([':nome' => $nome, ':email' => $email, ':area' => $area, ':origem' => $origem]);

    json_safe(['success' => true]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        json_safe(['success' => false, 'code' => 'duplicate_email'], 409);
    }
    json_safe(['success' => false, 'code' => 'db_error'], 500);
} catch (Throwable $e) {
    json_safe(['success' => false, 'code' => 'server_error'], 500);
}
