<?php
$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) { header('Location: /'); exit; }

$isExterior = isset($_GET['lang']) && $_GET['lang'] === 'en';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/lib/Database.php';
    setupSchema($pdo);

    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = :slug AND status = 'publicado' LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $post = null;
}

function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$title = $post ? ($isExterior ? $post['title_en'] : $post['title_pt']) : 'Post não encontrado';
$content = $post ? ($isExterior ? $post['content_en'] : $post['content_pt']) : '';
$excerpt = $post ? ($isExterior ? $post['excerpt_en'] : $post['excerpt_pt']) : '';
$author = $post ? $post['author'] : '';
$publishedAt = $post ? $post['published_at'] : '';
$pageTitle = $title . ' | Mondywork';
$pageDesc = $excerpt ? strip_tags($excerpt) : 'Leia o artigo: ' . $title;
$ogUrl = $isExterior
    ? 'https://mondywork.com.br/usa/blog/' . urlencode($slug)
    : 'https://mondywork.com.br/blog/' . urlencode($slug);
$canonical = $ogUrl;
$dateFormatted = $publishedAt
    ? ($isExterior ? date('M d, Y', strtotime($publishedAt)) : date('d/m/Y', strtotime($publishedAt)))
    : '';
$isoDate = $publishedAt ? date('Y-m-d', strtotime($publishedAt)) : '';
?><!DOCTYPE html>
<html lang="<?= $isExterior ? 'en' : 'pt-BR' ?>">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<?php if ($post): ?>
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="<?= esc($pageDesc) ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com.br/blog/<?= esc($slug) ?>">
<link rel="alternate" hreflang="en" href="https://mondywork.com.br/usa/blog/<?= esc($slug) ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= esc($ogUrl) ?>">
<meta property="og:title" content="<?= esc($pageTitle) ?>">
<meta property="og:description" content="<?= esc($pageDesc) ?>">
<meta property="og:image" content="https://mondywork.com.br/img/og-image.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="article:published_time" content="<?= $isoDate ?>">
<meta property="article:author" content="<?= esc($author) ?>">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= esc($ogUrl) ?>">
<meta property="twitter:title" content="<?= esc($pageTitle) ?>">
<meta property="twitter:description" content="<?= esc($pageDesc) ?>">
<meta property="twitter:image" content="https://mondywork.com.br/img/og-image.jpg">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?= esc($title) ?>",
  "description": "<?= esc($pageDesc) ?>",
  "author": { "@type": "Person", "name": "<?= esc($author) ?>" },
  "datePublished": "<?= $isoDate ?>",
  "publisher": { "@type": "Organization", "name": "Mondywork" }
}
</script>
<?php else: ?>
<title>Post não encontrado | Mondywork</title>
<meta name="robots" content="noindex">
<?php endif; ?>
<link rel="stylesheet" href="/css/style.css?v=1.0.6">
<link rel="icon" href="/img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="/img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/img/favicon/apple-touch-icon.png">
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RPQ9FFFNP1"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-RPQ9FFFNP1');
</script>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8069032517043297" crossorigin="anonymous"></script>
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <a class="nav-logo" href="/">Mondywork</a>
    <div class="nav-links">
      <a class="nav-link" href="<?= $isExterior ? '/usa/' : '/' ?>"><?= $isExterior ? 'Jobs' : 'Vagas' ?></a>
      <a class="nav-link active" href="<?= $isExterior ? '/usa/blog/' : '/blog/' ?>"><?= $isExterior ? 'Blog' : 'Blog' ?></a>
      <a class="nav-link" href="<?= $isExterior ? '/usa/about.html' : '/sobre.html' ?>"><?= $isExterior ? 'About' : 'Sobre' ?></a>
      <a class="nav-link" href="<?= $isExterior ? '/usa/contact.html' : '/contato.html' ?>"><?= $isExterior ? 'Contact' : 'Contato' ?></a>
      <?php if ($isExterior): ?>
      <a class="nav-link" href="/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
      <?php else: ?>
      <a class="nav-link" href="/usa/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA & worldwide</a>
      <?php endif; ?>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <a class="nav-link" href="<?= $isExterior ? '/usa/' : '/' ?>"><?= $isExterior ? 'Jobs' : 'Vagas' ?></a>
  <a class="nav-link active" href="<?= $isExterior ? '/usa/blog/' : '/blog/' ?>"><?= $isExterior ? 'Blog' : 'Blog' ?></a>
  <a class="nav-link" href="<?= $isExterior ? '/usa/about.html' : '/sobre.html' ?>"><?= $isExterior ? 'About' : 'Sobre' ?></a>
  <a class="nav-link" href="<?= $isExterior ? '/usa/contact.html' : '/contato.html' ?>"><?= $isExterior ? 'Contact' : 'Contato' ?></a>
  <a class="nav-link" href="<?= $isExterior ? '/usa/privacy.html' : '/privacidade.html' ?>"><?= $isExterior ? 'Privacy' : 'Privacidade' ?></a>
  <?php if ($isExterior): ?>
  <a class="nav-link" href="/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
  <?php endif; ?>
</div>

<main class="main-content" style="padding-top:80px">
  <div class="section" style="max-width:800px;margin:0 auto;padding:0 16px">

<?php if ($post): ?>

    <a href="<?= $isExterior ? '/usa/blog/' : '/blog/' ?>" class="job-card-btn" style="display:inline-flex;margin-bottom:24px;text-decoration:none">&larr; <?= $isExterior ? 'Back to blog' : 'Voltar para blog' ?></a>

    <article class="vaga-page">
      <header class="vaga-page-header">
        <h1 class="vaga-page-title"><?= esc($title) ?></h1>
        <div class="job-card-info" style="margin-top:12px">
          <span class="job-card-info-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= esc($author) ?>
          </span>
          <span class="job-card-info-text job-card-date">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <time datetime="<?= $isoDate ?>"><?= $dateFormatted ?></time>
          </span>
        </div>
      </header>

      <div class="vaga-page-body blog-content">
        <?= $content ?>
      </div>

      <div style="margin:32px 0">
        <ins class="adsbygoogle"
             style="display:block; text-align:center;"
             data-ad-layout="in-article"
             data-ad-format="fluid"
             data-ad-client="ca-pub-8069032517043297"
             data-ad-slot="2519706373"></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
      </div>

      <div class="vaga-page-footer" style="justify-content:flex-start">
        <a href="<?= $isExterior ? '/usa/blog/' : '/blog/' ?>" class="job-card-btn" style="text-decoration:none">&larr; <?= $isExterior ? 'Back to blog' : 'Voltar para blog' ?></a>
      </div>
    </article>

<?php else: ?>
    <div style="text-align:center;padding:80px 0">
      <h1 style="font-size:2rem;margin-bottom:16px"><?= $isExterior ? 'Post not found' : 'Post não encontrado' ?></h1>
      <p style="color:#666;margin-bottom:24px"><?= $isExterior ? 'This article is no longer available.' : 'Este artigo não está mais disponível.' ?></p>
      <a href="<?= $isExterior ? '/usa/blog/' : '/blog/' ?>" class="modal-btn" style="display:inline-block;text-decoration:none">&larr; <?= $isExterior ? 'Back to blog' : 'Voltar para blog' ?></a>
    </div>
<?php endif; ?>

  </div>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="<?= $isExterior ? '/usa/contact.html' : '/contato.html' ?>"><?= $isExterior ? 'Contact' : 'Contato' ?></a>
      <a class="footer-link" href="<?= $isExterior ? '/usa/about.html' : '/sobre.html' ?>"><?= $isExterior ? 'About' : 'Sobre' ?></a>
      <a class="footer-link" href="<?= $isExterior ? '/usa/privacy.html' : '/privacidade.html' ?>"><?= $isExterior ? 'Privacy' : 'Privacidade' ?></a>
      <a class="footer-link" href="<?= $isExterior ? '/usa/terms.html' : '/termos-de-uso.html' ?>"><?= $isExterior ? 'Terms' : 'Termos' ?></a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. <?= $isExterior ? 'All rights reserved.' : 'Todos os direitos reservados.' ?></p>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var navToggle = document.getElementById('nav-toggle');
  var mobileMenu = document.getElementById('mobile-menu');
  if (navToggle && mobileMenu) {
    navToggle.addEventListener('click', function() {
      navToggle.classList.toggle('active');
      mobileMenu.classList.toggle('open');
    });
    mobileMenu.querySelectorAll('a').forEach(function(link) {
      link.addEventListener('click', function() {
        navToggle.classList.remove('active');
        mobileMenu.classList.remove('open');
      });
    });
  }
});
</script>
</body>
</html>
