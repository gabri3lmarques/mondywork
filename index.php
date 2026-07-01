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

    $categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;

    $whereBlog = "status='publicado'";
    $paramsBlog = [];
    if ($categoriaFiltro) {
        $whereBlog .= " AND categoria = :cat";
        $paramsBlog[':cat'] = $categoriaFiltro;
    }

    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE $whereBlog");
    $totalStmt->execute($paramsBlog);
    $total = (int)$totalStmt->fetchColumn();
    $totalPages = max(1, (int)ceil($total / $limit));

    $stmt = $pdo->prepare("SELECT slug, title, excerpt, image, categoria, author, published_at, created_at FROM blog_posts WHERE $whereBlog ORDER BY published_at DESC LIMIT :lim OFFSET :off");
    foreach ($paramsBlog as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $todasCategorias = $pdo->query("SELECT DISTINCT categoria FROM blog_posts WHERE status='publicado' AND categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $posts = [];
    $total = 0;
    $totalPages = 1;
    $todasCategorias = [];
}

function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function excerpt($text, $max = 200) {
    if (mb_strlen($text) <= $max) return $text;
    return mb_substr($text, 0, $max) . '...';
}
function tempoLeitura($texto) {
    $palavras = str_word_count(strip_tags($texto), 0, 'àáâãéêíóôõúçÀÁÂÃÉÊÍÓÔÕÚÇ');
    return max(1, (int)ceil($palavras / 200));
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Mondywork | Carreira em Tecnologia, Design e Marketing</title>
<meta name="description" content="Artigos e guias sobre carreira, mercado de trabalho, dicas de entrevista e desenvolvimento profissional para quem trabalha com Tecnologia, Design, Marketing e Produto.">
<link rel="canonical" href="https://mondywork.com.br/">
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com.br/">
<link rel="alternate" hreflang="en" href="https://mondywork.com.br/usa/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com.br/">
<meta property="og:title" content="Mondywork | Carreira em Tecnologia, Design e Marketing">
<meta property="og:description" content="Artigos e guias sobre carreira, mercado de trabalho, dicas de entrevista e desenvolvimento profissional para quem trabalha com Tecnologia, Design, Marketing e Produto.">
<meta property="og:image" content="https://mondywork.com.br/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com.br/">
<meta property="twitter:title" content="Mondywork | Carreira em Tecnologia, Design e Marketing">
<meta property="twitter:description" content="Artigos e guias sobre carreira, mercado de trabalho, dicas de entrevista e desenvolvimento profissional para quem trabalha com Tecnologia, Design, Marketing e Produto.">
<meta property="twitter:image" content="https://mondywork.com.br/img/og-image.jpg">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Mondywork",
  "url": "https://mondywork.com.br/",
  "description": "Conteúdo sobre carreira em tecnologia, design, marketing e produto.",
  "inLanguage": "pt-BR"
}
</script>
<link rel="stylesheet" href="/css/style.css?v=1.3.0">
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
      <a class="nav-link" href="/sobre.html">Sobre</a>
      <a class="nav-link" href="/contato.html">Contato</a>
      <a class="nav-link" href="/usa/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA & worldwide</a>
    </div>
    <div class="nav-icon">
      <a aria-label="X (Twitter)" href="https://x.com/mondywork" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
      <a aria-label="LinkedIn" href="https://www.linkedin.com/company/mondywork/" target="_blank">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
      </a>
    </div>
    <button class="nav-toggle" id="nav-toggle" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <a class="nav-link" href="/vagas/">Vagas</a>
  <a class="nav-link" href="/sobre.html">Sobre</a>
  <a class="nav-link" href="/contato.html">Contato</a>
  <a class="nav-link" href="/usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
</div>

<main class="main-content">
  <section class="hero">
    <div class="hero-decor hero-decor-1"></div>
    <div class="hero-decor hero-decor-2"></div>
    <div class="hero-content">
      <h1 class="hero-title">Conteúdo para sua carreira</h1>
      <p class="hero-subtitle">Artigos, guias e dicas sobre carreira, mercado de trabalho e desenvolvimento profissional em Tecnologia, Design, Marketing e Produto.</p>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Artigos Recentes</h2>
    </div>

    <?php if (!empty($todasCategorias)): ?>
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:24px">
      <a href="/" class="cat-filter-checkbox" style="<?= $categoriaFiltro ? '' : 'background:#4b41e1;color:#fff;border-color:#4b41e1' ?>">Todas</a>
      <?php foreach ($todasCategorias as $cat): ?>
        <a href="?categoria=<?= urlencode($cat) ?>" class="cat-filter-checkbox" style="<?= $categoriaFiltro === $cat ? 'background:#4b41e1;color:#fff;border-color:#4b41e1' : '' ?>"><?= esc($cat) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="blog-grid">
      <?php if (empty($posts)): ?>
        <p style="color:#45464d;text-align:center;padding:48px 0;grid-column:1/-1">Nenhum artigo publicado ainda. Volte em breve!</p>
      <?php else: ?>
        <?php foreach ($posts as $p):
          $img = $p['image'] ?: '';
          $date = $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : date('d/m/Y', strtotime($p['created_at']));
          $leitura = tempoLeitura($p['excerpt'] ?: '');
        ?>
        <article class="blog-card">
          <a href="/blog/<?= esc($p['slug']) ?>" class="blog-card-link">
            <?php if ($img): ?>
              <div class="blog-card-image" style="background-image:url('<?= esc($img) ?>');background-size:cover;background-position:center"></div>
            <?php else: ?>
              <div class="blog-card-image" style="background:linear-gradient(135deg,#4b41e1,#7c75ff);display:flex;align-items:center;justify-content:center;color:#fff;font-size:2rem;font-weight:700">
                <?= esc(mb_substr($p['title'], 0, 1)) ?>
              </div>
            <?php endif; ?>
            <div class="blog-card-body">
              <?php if (!empty($p['categoria'])): ?>
                <span style="display:inline-block;font-size:11px;font-weight:700;color:#4b41e1;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px"><?= esc($p['categoria']) ?></span>
              <?php endif; ?>
              <h3 class="blog-card-title"><?= esc($p['title']) ?></h3>
              <p class="blog-card-excerpt"><?= esc(excerpt(strip_tags($p['excerpt'] ?: ''))) ?></p>
              <div class="blog-card-meta">
                <span><?= esc($p['author']) ?></span>
                <span><?= $date ?></span>
                <span><?= $leitura ?> min de leitura</span>
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

  <section class="section" style="padding-top:0">
    <div class="section-header">
      <h2 class="section-title">Guias de Carreira</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px">
      <div style="background:#f0effe;border-radius:12px;padding:32px">
        <h3 style="font-size:18px;margin:0 0 8px;color:#1c1d21"><a href="/guia-de-carreira.html" style="text-decoration:none;color:inherit">Guia de Carreira em Tecnologia</a></h3>
        <p style="font-size:14px;color:#45464d;margin:0 0 16px">Planejamento, habilidades, entrevistas e crescimento profissional em TI, Design, Marketing e Produto.</p>
        <a href="/guia-de-carreira.html" style="font-size:14px;font-weight:600;color:#4b41e1;text-decoration:none">Ler guia &rarr;</a>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="section-header">
      <h2 class="section-title">Encontre sua próxima vaga</h2>
    </div>
    <div style="text-align:center">
      <p style="color:#45464d;margin-bottom:24px;max-width:600px;margin-left:auto;margin-right:auto">Confira as vagas mais recentes em tecnologia, design, marketing e produto. Oportunidades atualizadas diariamente em todo o Brasil.</p>
      <a href="/vagas/" class="sidebar-btn" style="display:inline-block;width:auto;padding:16px 48px;text-decoration:none">Ver Vagas</a>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/contato.html">Contato</a>
      <a class="footer-link" href="/sobre.html">Sobre</a>
      <a class="footer-link" href="/guia-de-carreira.html">Guia de Carreira</a>
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
