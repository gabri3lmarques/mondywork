<?php
$config = require __DIR__ . '/config.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
    $config['user'],
    $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== CATEGORIAS ===" . PHP_EOL;
$rows = $pdo->query("SELECT id, slug, nome_pt, nome_en FROM categorias ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['id'] . ' | ' . $r['slug'] . ' | ' . $r['nome_pt'] . ' | ' . $r['nome_en'] . PHP_EOL;
}

echo PHP_EOL . "=== CATEGORIA_CONTEUDO ===" . PHP_EOL;
$rows2 = $pdo->query("SELECT cc.id, cc.categoria_id, c.slug FROM categoria_conteudo cc JOIN categorias c ON c.id = cc.categoria_id ORDER BY cc.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) {
    echo $r['id'] . ' | cat_id=' . $r['categoria_id'] . ' | slug=' . $r['slug'] . PHP_EOL;
}
