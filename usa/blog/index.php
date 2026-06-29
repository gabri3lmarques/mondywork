<?php
$configFile = file_exists(__DIR__ . '/../../config.local.php') ? __DIR__ . '/../../config.local.php' : __DIR__ . '/../../config.php';
$config = require $configFile;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/../../lib/Database.php';
    setupSchema($pdo);

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='publicado'");
    $total = (int)$totalStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $limit));

    $stmt = $pdo->prepare("SELECT slug, title_pt, title_en, excerpt_pt, excerpt_en, author, published_at, created_at FROM blog_posts WHERE status='publicado' ORDER BY published_at DESC LIMIT :lim OFFSET :off");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $posts = [];
    $total = 0;
    $totalPages = 1;
}

function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function excerpt($text, $max = 200) {
    if (mb_strlen($text) <= $max) return $text;
    return mb_substr($text, 0, $max) . '...';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Blog | Mondywork — Career in Tech, Design & Marketing</title>
<meta name="description" content="Articles about career, job market, interview tips and guides for Tech, Design, Marketing and Product professionals.">
<link rel="canonical" href="https://mondywork.com.br/usa/blog/">
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com.br/blog/">
<link rel="alternate" hreflang="en" href="https://mondywork.com.br/usa/blog/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com.br/usa/blog/">
<meta property="og:title" content="Blog | Mondywork — Career in Tech, Design & Marketing">
<meta property="og:description" content="Articles about career, job market, interview tips and guides for Tech, Design, Marketing and Product professionals.">
<meta property="og:image" content="https://mondywork.com.br/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com.br/usa/blog/">
<meta property="twitter:title" content="Blog | Mondywork — Career in Tech, Design & Marketing">
<meta property="twitter:description" content="Articles about career, job market, interview tips and guides for Tech, Design, Marketing and Product professionals.">
<meta property="twitter:image" content="https://mondywork.com.br/img/og-image.jpg">
<link rel="stylesheet" href="/css/style.css?v=1.0.8">
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
</head>
<body>
<script>
  atOptions = {
    'key' : '42bd95ac1f5bc41ae0232917b44a6c69',
    'format' : 'iframe',
    'height' : 90,
    'width' : 728,
    'params' : {}
  };
</script>
<script src="https://www.highperformanceformat.com/42bd95ac1f5bc41ae0232917b44a6c69/invoke.js"></script>


<nav class="nav">
  <div class="nav-inner">
    <a class="nav-logo" href="/">Mondywork</a>
    <div class="nav-links">
      <a class="nav-link" href="/usa/">Jobs</a>
      <a class="nav-link active" href="/usa/blog/">Blog</a>
      <a class="nav-link" href="/usa/about.html">About</a>
      <a class="nav-link" href="/usa/contact.html">Contact</a>
      <a class="nav-link" href="/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <a class="nav-link" href="/usa/">Jobs</a>
  <a class="nav-link active" href="/usa/blog/">Blog</a>
  <a class="nav-link" href="/usa/about.html">About</a>
  <a class="nav-link" href="/usa/contact.html">Contact</a>
  <a class="nav-link" href="/usa/privacy.html">Privacy</a>
  <a class="nav-link" href="/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
</div>

<main class="main-content">
  <section class="hero" style="padding-bottom:0">
    <div class="hero-content">
      <h1 class="hero-title">Blog</h1>
      <p class="hero-subtitle">Articles about career, job market, and professional development in Tech, Design, Marketing, and Product.</p>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Latest Articles</h2>
    </div>

    <div class="blog-grid">
      <?php if (empty($posts)): ?>
        <p style="color:#45464d;text-align:center;padding:48px 0;grid-column:1/-1">No articles published yet. Check back soon!</p>
      <?php else: ?>
        <?php foreach ($posts as $p):
          $date = $p['published_at'] ? date('M d, Y', strtotime($p['published_at'])) : date('M d, Y', strtotime($p['created_at']));
        ?>
        <article class="blog-card">
          <a href="/usa/blog/<?= esc($p['slug']) ?>" class="blog-card-link">
            <div class="blog-card-image" style="background:linear-gradient(135deg,#4b41e1,#7c75ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700">
              <?= esc(mb_substr($p['title_en'], 0, 1)) ?>
            </div>
            <div class="blog-card-body">
              <h3 class="blog-card-title"><?= esc($p['title_en']) ?></h3>
              <p class="blog-card-excerpt"><?= esc(excerpt(strip_tags($p['excerpt_en'] ?: $p['content_en']))) ?></p>
              <div class="blog-card-meta">
                <span><?= esc($p['author']) ?></span>
                <span><?= $date ?></span>
              </div>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="admin-pagination" style="margin-top:48px">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">&laquo; Previous</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Next &raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </section>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/usa/contact.html">Contact</a>
      <a class="footer-link" href="/usa/about.html">About</a>
      <a class="footer-link" href="/usa/privacy.html">Privacy</a>
      <a class="footer-link" href="/usa/terms.html">Terms</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. All rights reserved.</p>
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
