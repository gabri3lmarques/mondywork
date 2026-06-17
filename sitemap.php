<?php
$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/lib/Database.php';
    setupSchema($pdo);

    $stmt = $pdo->query("SELECT vaga_id_externo, publicado_em FROM vagas WHERE status = 'ativa' ORDER BY publicado_em DESC");
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $vagas = [];
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://mondywork.com.br/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/br/</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/usa/</loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/sobre.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/contato.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/privacidade.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/termos-de-uso.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/usa/about.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/usa/contact.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/usa/privacy.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com.br/usa/terms.html</loc>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
<?php foreach ($vagas as $v):
    $lastmod = $v['publicado_em'] ? date('Y-m-d', strtotime($v['publicado_em'])) : date('Y-m-d');
?>
  <url>
    <loc>https://mondywork.com.br/vaga/<?= htmlspecialchars($v['vaga_id_externo'], ENT_XML1, 'UTF-8') ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
