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

    $stmtBlog = $pdo->query("SELECT slug, published_at FROM blog_posts WHERE status = 'publicado' ORDER BY published_at DESC");
    $blogPosts = $stmtBlog->fetchAll(PDO::FETCH_ASSOC);

    $stmtVagas = $pdo->query("SELECT vaga_id_externo, publicado_em FROM vagas WHERE status = 'ativa' ORDER BY publicado_em DESC");
    $vagas = $stmtVagas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $blogPosts = [];
    $vagas = [];
}

if (!function_exists('getLastmod')) {
function getLastmod($file) {
    return date('Y-m-d');
}
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
    <loc>https://mondywork.com/sobre.php</loc>
    <lastmod><?= getLastmod('sobre.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com/vagas/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc>https://mondywork.com/contato.php</loc>
    <lastmod><?= getLastmod('contato.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/privacidade.php</loc>
    <lastmod><?= getLastmod('privacidade.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/termos-de-uso.php</loc>
    <lastmod><?= getLastmod('termos-de-uso.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-design.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-design.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-marketing.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-marketing.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-produto.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-produto.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-financas.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-financas.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-dados.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-dados.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-comunicacao.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-comunicacao.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/guia-de-carreira-administracao.php</loc>
    <lastmod><?= getLastmod('guia-de-carreira-administracao.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/about.php</loc>
    <lastmod><?= getLastmod('usa/about.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/contact.php</loc>
    <lastmod><?= getLastmod('usa/contact.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/privacy.php</loc>
    <lastmod><?= getLastmod('usa/privacy.php') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>https://mondywork.com/usa/terms.php</loc>
    <lastmod><?= getLastmod('usa/terms.php') ?></lastmod>
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
    $vagaLastmod = $v['publicado_em'] ? date('Y-m-d', strtotime($v['publicado_em'])) : date('Y-m-d');
?>
  <url>
    <loc>https://mondywork.com/vaga/<?= htmlspecialchars($v['vaga_id_externo'], ENT_XML1, 'UTF-8') ?></loc>
    <lastmod><?= $vagaLastmod ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
