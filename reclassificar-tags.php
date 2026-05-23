<?php
/**
 * Reclassifica todas as vagas existentes com base no dicionário de categorias.
 * Não ativa nem inativa vagas — apenas recria as tags em vaga_categorias.
 * Uso: php reclassificar-tags.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/VagaRepository.php';
require __DIR__ . '/categorias.php';

$configFile = __DIR__ . '/config.local.php';
$dbConfig = require file_exists($configFile) ? $configFile : __DIR__ . '/config.php';

try {
    $pdo = conectarBanco($dbConfig);
    setupSchema($pdo);

    $contagem = $pdo->query("SELECT COUNT(*) FROM vagas")->fetchColumn();
    echo "Reclassificando {$contagem} vagas...\n\n";

    $stmt = $pdo->query("SELECT id, titulo FROM vagas ORDER BY id");
    $total = 0;

    while ($vaga = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $total++;
        $tags = classificarVaga($vaga['titulo'], $categorias_mondywork);
        salvarTagsVaga($pdo, (int)$vaga['id'], $tags);
        echo "  [{$total}] ID {$vaga['id']} → {$vaga['titulo']}\n";
    }

    echo "\nConcluido! {$total} vagas reclassificadas.\n";

} catch (Exception $e) {
    echo "\n[ERRO] " . $e->getMessage() . "\n";
    exit(1);
}
