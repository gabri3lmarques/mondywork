<?php
$configFile = file_exists(__DIR__ . '/../config.local.php') ? __DIR__ . '/../config.local.php' : __DIR__ . '/../config.php';
$config = require $configFile;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/../lib/Database.php';
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
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Blog | Mondywork — Carreira em Tecnologia, Design e Marketing</title>
<meta name="description" content="Artigos sobre carreira, mercado de trabalho, dicas de entrevista e guias para profissionais de Tecnologia, Design, Marketing e Produto.">
<link rel="canonical" href="https://mondywork.com.br/blog/">
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com.br/blog/">
<link rel="alternate" hreflang="en" href="https://mondywork.com.br/usa/blog/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com.br/blog/">
<meta property="og:title" content="Blog | Mondywork — Carreira em Tecnologia, Design e Marketing">
<meta property="og:description" content="Artigos sobre carreira, mercado de trabalho, dicas de entrevista e guias para profissionais de Tecnologia, Design, Marketing e Produto.">
<meta property="og:image" content="https://mondywork.com.br/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com.br/blog/">
<meta property="twitter:title" content="Blog | Mondywork — Carreira em Tecnologia, Design e Marketing">
<meta property="twitter:description" content="Artigos sobre carreira, mercado de trabalho, dicas de entrevista e guias para profissionais de Tecnologia, Design, Marketing e Produto.">
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

<nav class="nav">
  <div class="nav-inner">
    <a class="nav-logo" href="/">Mondywork</a>
    <div class="nav-links">
      <a class="nav-link" href="/">Vagas</a>
      <a class="nav-link active" href="/blog/">Blog</a>
      <a class="nav-link" href="/sobre.html">Sobre</a>
      <a class="nav-link" href="/contato.html">Contato</a>
      <a class="nav-link" href="/usa/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA & worldwide</a>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <a class="nav-link" href="/">Vagas</a>
  <a class="nav-link active" href="/blog/">Blog</a>
  <a class="nav-link" href="/sobre.html">Sobre</a>
  <a class="nav-link" href="/contato.html">Contato</a>
  <a class="nav-link" href="/privacidade.html">Privacidade</a>
  <a class="nav-link" href="/usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
</div>

<main class="main-content">
  <section class="hero" style="padding-bottom:0">
    <div class="hero-content">
      <h1 class="hero-title">Blog</h1>
      <p class="hero-subtitle">Artigos sobre carreira, mercado de trabalho e desenvolvimento profissional em Tecnologia, Design, Marketing e Produto.</p>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Artigos Recentes</h2>
    </div>

    <div class="blog-grid">
      <?php if (empty($posts)): ?>
        <p style="color:#45464d;text-align:center;padding:48px 0;grid-column:1/-1">Nenhum artigo publicado ainda. Volte em breve!</p>
      <?php else: ?>
        <?php foreach ($posts as $p):
          $img = '/img/og-image.jpg';
          $date = $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : date('d/m/Y', strtotime($p['created_at']));
        ?>
        <article class="blog-card">
          <a href="/blog/<?= esc($p['slug']) ?>" class="blog-card-link">
            <div class="blog-card-image" style="background:linear-gradient(135deg,#4b41e1,#7c75ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700">
              <?= esc(mb_substr($p['title_pt'], 0, 1)) ?>
            </div>
            <div class="blog-card-body">
              <h3 class="blog-card-title"><?= esc($p['title_pt']) ?></h3>
              <p class="blog-card-excerpt"><?= esc(excerpt(strip_tags($p['excerpt_pt'] ?: $p['content_pt']))) ?></p>
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
        <a href="?page=<?= $page - 1 ?>">&laquo; Anterior</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Próximo &raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </section>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/contato.html">Contato</a>
      <a class="footer-link" href="/sobre.html">Sobre</a>
      <a class="footer-link" href="/privacidade.html">Privacidade</a>
      <a class="footer-link" href="/termos-de-uso.html">Termos</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. Todos os direitos reservados.</p>
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
