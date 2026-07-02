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

    $stmt = $pdo->query("SELECT vaga_id_externo, publicado_em, origem FROM vagas WHERE status = 'ativa' ORDER BY publicado_em DESC");
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtBlog = $pdo->query("SELECT slug, published_at FROM blog_posts WHERE status = 'publicado' ORDER BY published_at DESC");
    $blogPosts = $stmtBlog->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $vagas = [];
    $blogPosts = [];
}

function getLastmod($file) {
    $path = __DIR__ . '/' . $file;
    return file_exists($path) ? date('Y-m-d', filemtime($path)) : date('Y-m-d');
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://mondywork.com/</loc>
    <lastmod><?= getLastmod('index.php') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://mondywork.com/vagas/</loc>
    <lastmod><?= getLastmod('vagas/index.php') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/</loc>
    <lastmod><?= getLastmod('usa/index.php') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://mondywork.com/sobre.html</loc>
    <lastmod><?= getLastmod('sobre.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com/contato.html</loc>
    <lastmod><?= getLastmod('contato.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/privacidade.html</loc>
    <lastmod><?= getLastmod('privacidade.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/termos-de-uso.html</loc>
    <lastmod><?= getLastmod('termos-de-uso.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-design.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-design.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-marketing.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-marketing.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-produto.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-produto.html') ?></lastmod>
    <changefreq>monthly</changefreq>

    <loc>https://mondywork.com/guia-de-carreira-financas.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-financas.html') ?></lastmod>
    <changefreq>monthly</changefreq>

    <loc>https://mondywork.com/guia-de-carreira-dados.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-dados.html') ?></lastmod>
    <changefreq>monthly</changefreq>

    <loc>https://mondywork.com/guia-de-carreira-comunicacao.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-comunicacao.html') ?></lastmod>
    <changefreq>monthly</changefreq>

    <loc>https://mondywork.com/guia-de-carreira-administracao.html</loc>
    <lastmod><?= getLastmod('guia-de-carreira-administracao.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/about.html</loc>
    <lastmod><?= getLastmod('usa/about.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/contact.html</loc>
    <lastmod><?= getLastmod('usa/contact.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/privacy.html</loc>
    <lastmod><?= getLastmod('usa/privacy.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/terms.html</loc>
    <lastmod><?= getLastmod('usa/terms.html') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
<?php foreach ($blogPosts as $b):
    $blogLastmod = $b['published_at'] ? date('Y-m-d', strtotime($b['published_at'])) : date('Y-m-d');
?>
  <url>
    <loc>https://mondywork.com/blog/<?= htmlspecialchars($b['slug'], ENT_XML1, 'UTF-8') ?></loc>
    <lastmod><?= $blogLastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
<?php endforeach; ?>
<?php foreach ($vagas as $v):
    $lastmod = $v['publicado_em'] ? date('Y-m-d', strtotime($v['publicado_em'])) : date('Y-m-d');
    $baseUrl = $v['origem'] === 'exterior' ? 'https://mondywork.com/usa/vaga/' : 'https://mondywork.com/vaga/';
?>
  <url>
    <loc><?= $baseUrl . htmlspecialchars($v['vaga_id_externo'], ENT_XML1, 'UTF-8') ?></loc>
    <lastmod><?= $lastmod ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
