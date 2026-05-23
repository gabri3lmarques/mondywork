<?php
/**
 * Reclassifica todas as vagas existentes com base no dicionário de categorias.
 * Não ativa nem inativa vagas — apenas recria as tags em vaga_categorias.
 * Uso: php reclassificar-tags.php
 */

require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';
require __DIR__ . '/categorias.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    $stmt = $pdo->query("SELECT id, titulo FROM vagas ORDER BY id");
    $total = $stmt->rowCount();
    $contador = 0;

    echo "Reclassificando {$total} vagas...\n\n";

    while ($vaga = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $contador++;
        $tags = classificarVaga($vaga['titulo'], $categorias_mondywork);
        salvarTagsVaga($pdo, (int)$vaga['id'], $tags);
        echo sprintf("  [%4d/%d] ID %d → %s\n", $contador, $total, $vaga['id'], $vaga['titulo']);
    }

    echo "\nConcluído! {$total} vagas reclassificadas.\n";

} catch (Exception $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
