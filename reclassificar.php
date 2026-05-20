<?php
/**
 * Reclassificador de vagas existentes.
 * Processa todas as vagas ativas e inativas, reclassificando com o algoritmo atual.
 */
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';
require __DIR__ . '/lib/ClassificadorVaga.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    echo "[" . date('Y-m-d H:i:s') . "] Reclassificando todas as vagas...\n\n";

    $stmt = $pdo->query("SELECT id, vaga_id_externo, titulo, empresa, descricao, status, area FROM vagas ORDER BY id");
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($vagas);

    if ($total === 0) {
        echo "Nenhuma vaga encontrada.\n";
        exit;
    }

    echo "Total de vagas: $total\n\n";

    $stats = [
        'aprovadas' => 0,
        'rejeitadas' => 0,
        'reaplicadas' => 0,
        'inalteradas' => 0,
    ];

    $stmtUpdate = $pdo->prepare("UPDATE vagas SET status = :status, area = :area WHERE id = :id");

    foreach ($vagas as $vaga) {
        $classif = classificarVaga($vaga['titulo'], $vaga['descricao'] ?? '');

        $novoStatus = $classif['aprovada'] ? 'ativa' : 'inativa';
        $novaArea = $classif['aprovada'] ? $classif['area'] : null;

        $mudou = ($vaga['status'] !== $novoStatus) || ($vaga['area'] !== $novaArea);

        if (!$mudou) {
            $stats['inalteradas']++;
            continue;
        }

        $stmtUpdate->execute([':status' => $novoStatus, ':area' => $novaArea, ':id' => $vaga['id']]);

        if ($classif['aprovada']) {
            if ($vaga['status'] === 'inativa') {
                echo "  [REAPLICADA] {$classif['area']} :: {$vaga['titulo']} ({$vaga['empresa']})\n";
                $stats['reaplicadas']++;
            } else {
                echo "  [APROVADA] {$classif['area']} :: {$vaga['titulo']} ({$vaga['empresa']})\n";
                $stats['aprovadas']++;
            }
        } else {
            echo "  [REJEITADA] {$vaga['titulo']} ({$vaga['empresa']}) - {$classif['motivo']}\n";
            $stats['rejeitadas']++;
        }
    }

    echo "\n[" . date('Y-m-d H:i:s') . "] Reclassificacao concluida.\n";
    echo "  Inalteradas: {$stats['inalteradas']}\n";
    echo "  Aprovadas: {$stats['aprovadas']}\n";
    echo "  Reaplicadas (estavam inativas): {$stats['reaplicadas']}\n";
    echo "  Rejeitadas: {$stats['rejeitadas']}\n";

} catch (Exception $e) {
    echo "\n[ERRO FATAL] " . $e->getMessage() . "\n";
}
