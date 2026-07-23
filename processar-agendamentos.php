<?php
/**
 * Worker de Processamento de Agendamentos de Vagas (Mondywork)
 * Pode ser executado via Cron Job (ex: * * * * * php /caminho/processar-agendamentos.php)
 */
require_once __DIR__ . '/lib/Database.php';

$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$dbConfig = require $configFile;

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    $processados = processarAgendamentosVagas($pdo);
    if ($processados > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Agendamentos processados: {$processados} vaga(s) atualizada(s).\n";
    }
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Erro ao processar agendamentos: " . $e->getMessage() . "\n";
}
