<?php
$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) { header('Location: /'); exit; }

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
function tempoLeitura($texto) {
    $palavras = str_word_count(strip_tags($texto), 0, 'àáâãéêíóôõúçÀÁÂÃÉÊÍÓÔÕÚÇ');
    return max(1, (int)ceil($palavras / 200));
}

$title = $post ? $post['title'] : 'Post não encontrado';
$content = $post ? $post['content'] : '';
$excerpt = $post ? $post['excerpt'] : '';
$author = $post ? $post['author'] : '';
$publishedAt = $post ? $post['published_at'] : '';
$image = $post ? ($post['image'] ?? '') : '';
$categoria = $post ? ($post['categoria'] ?? '') : '';
$leitura = $post ? tempoLeitura($post['content'] ?? '') : 0;
$pageTitle = $title . ' | Mondywork';
$pageDesc = $excerpt ? strip_tags($excerpt) : 'Leia o artigo: ' . $title;
$ogImage = $image ?: 'https://mondywork.com/img/og-image.jpg';
$ogUrl = 'https://mondywork.com/blog/' . urlencode($slug);
$canonical = $ogUrl;
$dateFormatted = $publishedAt ? date('d/m/Y', strtotime($publishedAt)) : '';
$isoDate = $publishedAt ? date('Y-m-d', strtotime($publishedAt)) : '';

$relacionados = [];
if ($post && $pdo) {
    try {
        $stmtRel = $pdo->prepare("SELECT slug, title, excerpt, image, published_at FROM blog_posts WHERE status='publicado' AND id != :id AND categoria = :cat ORDER BY published_at DESC LIMIT 3");
        $stmtRel->execute([':id' => $post['id'], ':cat' => $post['categoria']]);
        $relacionados = $stmtRel->fetchAll(PDO::FETCH_ASSOC);
        if (empty($relacionados)) {
            $stmtRel = $pdo->prepare("SELECT slug, title, excerpt, image, published_at FROM blog_posts WHERE status='publicado' AND id != :id ORDER BY published_at DESC LIMIT 3");
            $stmtRel->execute([':id' => $post['id']]);
            $relacionados = $stmtRel->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {}
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<?php if ($post): ?>
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="<?= esc($pageDesc) ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= esc($ogUrl) ?>">
<meta property="og:title" content="<?= esc($pageTitle) ?>">
<meta property="og:description" content="<?= esc($pageDesc) ?>">
<meta property="og:image" content="<?= esc($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="article:published_time" content="<?= $isoDate ?>">
<meta property="article:author" content="<?= esc($author) ?>">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= esc($ogUrl) ?>">
<meta property="twitter:title" content="<?= esc($pageTitle) ?>">
<meta property="twitter:description" content="<?= esc($pageDesc) ?>">
<meta property="twitter:image" content="<?= esc($ogImage) ?>">
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
<link rel="stylesheet" href="/css/style.css?v=1.5.0">
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
      <a class="nav-link" href="/vagas/">Vagas</a>
      <a class="nav-link active" href="/">Blog</a>
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
  <a class="nav-link" href="/vagas/">Vagas</a>
  <a class="nav-link active" href="/">Blog</a>
  <a class="nav-link" href="/sobre.html">Sobre</a>
  <a class="nav-link" href="/contato.html">Contato</a>
  <a class="nav-link" href="/usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
</div>

<main class="main-content" style="padding-top:80px">
  <div class="section" style="max-width:800px;margin:0 auto;padding:0 16px">

<?php if ($post): ?>

    <a href="/" class="job-card-btn" style="display:inline-flex;margin-bottom:24px;text-decoration:none">&larr; Voltar</a>

    <article class="vaga-page">
      <?php if ($image): ?>
        <div style="width:100%;height:320px;border-radius:0.75rem;overflow:hidden;margin-bottom:24px;background:#f0f0f5">
          <img src="<?= esc($image) ?>" alt="<?= esc($title) ?>" style="width:100%;height:100%;object-fit:cover">
        </div>
      <?php endif; ?>

      <header class="vaga-page-header">
        <?php if ($categoria): ?>
          <span style="display:inline-block;font-size:12px;font-weight:700;color:#4b41e1;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px"><?= esc($categoria) ?></span>
        <?php endif; ?>
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
          <span class="job-card-info-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?= $leitura ?> min de leitura
          </span>
        </div>
      </header>

      <div class="vaga-page-body blog-content">
        <?= $content ?>
      </div>

      <?php if (!empty($relacionados)): ?>
      <div style="margin-top:64px;padding-top:32px;border-top:1px solid #c6c6cd">
        <h3 style="font-size:1.25rem;font-weight:700;margin-bottom:20px">Artigos Relacionados</h3>
        <div class="blog-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
          <?php foreach ($relacionados as $r):
            $rDate = $r['published_at'] ? date('d/m/Y', strtotime($r['published_at'])) : '';
            $rImg = $r['image'] ?? '';
          ?>
          <article class="blog-card">
            <a href="/blog/<?= esc($r['slug']) ?>" class="blog-card-link">
              <?php if ($rImg): ?>
                <div class="blog-card-image" style="background-image:url('<?= esc($rImg) ?>');background-size:cover;background-position:center;height:140px"></div>
              <?php else: ?>
                <div class="blog-card-image" style="background:linear-gradient(135deg,#4b41e1,#7c75ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;font-weight:700;height:140px">
                  <?= esc(mb_substr($r['title'], 0, 1)) ?>
                </div>
              <?php endif; ?>
              <div class="blog-card-body">
                <h3 class="blog-card-title" style="font-size:0.95rem"><?= esc($r['title']) ?></h3>
                <?php if ($rDate): ?>
                  <div class="blog-card-meta" style="margin-top:8px">
                    <span><?= $rDate ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="vaga-page-footer" style="justify-content:flex-start;margin-top:32px">
        <a href="/" class="job-card-btn" style="text-decoration:none">&larr; Voltar</a>
      </div>
    </article>

<?php else: ?>
    <div style="text-align:center;padding:80px 0">
      <h1 style="font-size:2rem;margin-bottom:16px">Post não encontrado</h1>
      <p style="color:#666;margin-bottom:24px">Este artigo não está mais disponível.</p>
      <a href="/" class="modal-btn" style="display:inline-block;text-decoration:none">&larr; Voltar</a>
    </div>
<?php endif; ?>

  </div>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/contato.html">Contato</a>
      <a class="footer-link" href="/sobre.html">Sobre</a>
      <a class="footer-link" href="/guia-de-carreira.html">Guia de Tecnologia</a>
      <a class="footer-link" href="/guia-de-carreira-design.html">Guia de Design</a>
      <a class="footer-link" href="/guia-de-carreira-marketing.html">Guia de Marketing</a>
      <a class="footer-link" href="/guia-de-carreira-comunicacao.html">Guia de Comunicacao</a>
      <a class="footer-link" href="/guia-de-carreira-administracao.html">Guia de Administracao</a>
      <a class="footer-link" href="/guia-de-carreira-financas.html">Guia de Finanças</a>
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
