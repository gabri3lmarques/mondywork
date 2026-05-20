<?php
/**
 * Reclassificador de vagas existentes.
 * Processa apenas vagas sem area definida ou com status 'inativa'.
 * Vagas já aprovadas com area são ignoradas.
 */
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';
require __DIR__ . '/lib/ClassificadorVaga.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    echo "[" . date('Y-m-d H:i:s') . "] Reclassificando vagas...\n\n";

    // Vagas ativas sem area (nunca classificadas ou backfill pendente)
    $stmt = $pdo->query("SELECT id, vaga_id_externo, titulo, empresa, descricao, status, area FROM vagas WHERE area IS NULL OR status = 'inativa' ORDER BY id");
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($vagas)) {
        echo "Nenhuma vaga pendente de reclassificacao.\n";
        exit;
    }

    echo "Total de vagas para processar: " . count($vagas) . "\n\n";

    $stats = [
        'aprovadas' => 0,
        'rejeitadas' => 0,
        'reaplicadas' => 0,  // estava inativa, agora aprovada
        'erro' => 0,
    ];

    $stmtUpdate = $pdo->prepare("UPDATE vagas SET status = :status, area = :area WHERE id = :id");

    foreach ($vagas as $vaga) {
        $classif = classificarVaga($vaga['titulo'], $vaga['descricao'] ?? '');

        $novoStatus = $classif['aprovada'] ? 'ativa' : 'inativa';
        $novaArea = $classif['aprovada'] ? $classif['area'] : null;

        $mudou = ($vaga['status'] !== $novoStatus) || ($vaga['area'] !== $novaArea);

        if (!$mudou && $classif['aprovada']) {
            continue; // já estava aprovada com area, pula
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
    echo "  Aprovadas: {$stats['aprovadas']}\n";
    echo "  Reaplicadas (estavam inativas): {$stats['reaplicadas']}\n";
    echo "  Rejeitadas: {$stats['rejeitadas']}\n";

} catch (Exception $e) {
    echo "\n[ERRO FATAL] " . $e->getMessage() . "\n";
}
