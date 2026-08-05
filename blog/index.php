<?php
require_once __DIR__ . '/../App/Autoloader.php';
$configFile = file_exists(__DIR__ . '/../config.local.php') ? __DIR__ . '/../config.local.php' : __DIR__ . '/../config.php';
$config = require $configFile;

try {
    require_once __DIR__ . '/../lib/Database.php';
    $pdo = conectarBanco($config);
    setupSchema($pdo);
    processarAgendamentosVagas($pdo);

    $categoriaFiltro = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 24;
    $offset = ($page - 1) * $limit;

    $whereBlog = "status='publicado' AND lang='pt'";
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

    $todasCategorias = $pdo->query("SELECT DISTINCT categoria FROM blog_posts WHERE status='publicado' AND lang='pt' AND categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

    $totalVagas = (int)$pdo->query("SELECT COUNT(*) FROM vagas WHERE status='ativa' AND is_nao_listada = 0")->fetchColumn();
    $totalArtigos = (int)$pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status='publicado' AND lang='pt'")->fetchColumn();
    $empresas = (int)$pdo->query("SELECT COUNT(DISTINCT empresa) FROM vagas WHERE status='ativa' AND is_nao_listada = 0")->fetchColumn();
} catch (Exception $e) {
    $posts = [];
    $total = 0;
    $totalPages = 1;
    $todasCategorias = [];
    $totalVagas = 0;
    $totalArtigos = 0;
    $empresas = 0;
}

if (!function_exists('esc')) {
    function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('excerpt')) {
    function excerpt($text, $max = 350) {
        if (mb_strlen($text) <= $max) return $text;
        return mb_substr($text, 0, $max) . '...';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Blog | Mondywork - Carreira em Tecnologia, Design, Marketing e mais</title>
<meta name="description" content="Artigos e guias sobre carreira, entrevistas e desenvolvimento profissional em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto.">
<link rel="canonical" href="https://mondywork.com/blog/">
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com/blog/">
<link rel="alternate" hreflang="en" href="https://mondywork.com/usa/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com/blog/">
<meta property="og:title" content="Blog | Mondywork - Carreira em Tecnologia, Design, Marketing e mais">
<meta property="og:description" content="Artigos e guias sobre carreira, entrevistas e desenvolvimento profissional em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto.">
<meta property="og:image" content="https://mondywork.com/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com/blog/">
<meta property="twitter:title" content="Blog | Mondywork - Carreira em Tecnologia, Design, Marketing e mais">
<meta property="twitter:description" content="Artigos e guias sobre carreira, entrevistas e desenvolvimento profissional em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto.">
<meta property="twitter:image" content="https://mondywork.com/img/og-image.jpg">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Mondywork Blog",
  "url": "https://mondywork.com/blog/",
  "description": "Conteúdo sobre carreira em tecnologia, design, marketing, comunicacao, administracao, financas, dados e produto.",
  "inLanguage": "pt-BR"
}
</script>
<link rel="stylesheet" href="/css/style.css?v=2.2.1">
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
      <a class="nav-link nav-btn" href="/">Vagas</a>
      <a class="nav-link active" href="/blog/">Blog</a>
      <a class="nav-link" href="/sobre.php">Sobre</a>
      <a class="nav-link" href="/contato.php">Contato</a>
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
  <a class="nav-link nav-btn" href="/">Vagas</a>
  <a class="nav-link active" href="/blog/">Blog</a>
  <a class="nav-link" href="/sobre.php">Sobre</a>
  <a class="nav-link" href="/contato.php">Contato</a>
  <a class="nav-link" href="/usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
</div>

<main class="main-content">
  <section class="hero">
    <div class="hero-decor hero-decor-1"></div>
    <div class="hero-decor hero-decor-2"></div>
    <div class="hero-content">
      <h1 class="hero-title">Conteúdo para sua carreira</h1>
      <p class="hero-subtitle">Artigos, guias e dicas sobre carreira, mercado de trabalho e desenvolvimento profissional em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto.</p>
    </div>
  </section>

  <section class="about-section">
    <div class="about-content">
      <p>O <strong>Mondywork</strong> é um portal independente que ajuda profissionais de tecnologia, design, marketing e produto a construírem carreiras de sucesso. Publicamos semanalmente artigos originais sobre planejamento profissional, preparação para entrevistas, desenvolvimento de habilidades, tendências do mercado e estratégias de crescimento.</p>
      <p>Nosso compromisso é oferecer conteúdo prático e relevante, escrito por profissionais que vivem o dia a dia do mercado. Aqui você encontra desde guias completos sobre cada área de atuação até dicas objetivas para acelerar sua trajetória profissional.</p>
    </div>
  </section>

  <section class="section stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-number"><?= number_format($totalVagas) ?>+</span>
        <span class="stat-label">Vagas de emprego</span>
      </div>
      <div class="stat-card">
        <span class="stat-number"><?= $totalArtigos ?>+</span>
        <span class="stat-label">Artigos publicados</span>
      </div>
      <div class="stat-card">
        <span class="stat-number"><?= number_format($empresas) ?>+</span>
        <span class="stat-label">Empresas parceiras</span>
      </div>
      <div class="stat-card">
        <span class="stat-number">8</span>
        <span class="stat-label">Guias de carreira</span>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Artigos Recentes</h2>
    </div>
    <p class="section-description">Confira os artigos mais recentes do nosso blog. Abordamos carreira, mercado de trabalho, entrevistas, desenvolvimento profissional e tendências em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto.</p>

    <?php if (!empty($todasCategorias)): ?>
    <div class="cat-filter">
      <a href="/blog/" class="<?= $categoriaFiltro ? '' : 'active' ?>">Todas</a>
      <?php foreach ($todasCategorias as $cat): ?>
        <a href="?categoria=<?= urlencode($cat) ?>" class="<?= $categoriaFiltro === $cat ? 'active' : '' ?>"><?= esc($cat) ?></a>
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
        ?>
        <article class="blog-card">
          <a href="/blog/<?= esc($p['slug']) ?>" class="blog-card-link">
            <?php if ($img): ?>
              <div class="blog-card-image" style="background-image:url('<?= esc($img) ?>')"></div>
            <?php else: ?>
              <div class="blog-card-image blog-card-image--empty"><?= esc(mb_substr($p['title'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="blog-card-body">
              <?php if (!empty($p['categoria'])): ?>
                <span class="blog-card-cat"><?= esc($p['categoria']) ?></span>
              <?php endif; ?>
              <h3 class="blog-card-title"><?= esc($p['title']) ?></h3>
              <p class="blog-card-excerpt"><?= esc(excerpt(strip_tags($p['excerpt'] ?: ''))) ?></p>
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
    <?php $catParam = $categoriaFiltro ? '&categoria=' . urlencode($categoriaFiltro) : ''; ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 . $catParam ?>">&laquo; Anterior</a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?page=<?= $i . $catParam ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 . $catParam ?>">Próximo &raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Como funciona</h2>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">1</div>
        <h3 class="step-title">Busque vagas</h3>
        <p class="step-text">Pesquise por cargo, palavra-chave ou habilidade. Nossa busca inteligente encontra as melhores oportunidades para você em segundos, com correção ortográfica integrada.</p>
      </div>
      <div class="step-card">
        <div class="step-number">2</div>
        <h3 class="step-title">Explore conteúdos</h3>
        <p class="step-text">Leia artigos e guias sobre carreira, entrevistas e desenvolvimento profissional. Conteúdo original escrito por profissionais que entendem do mercado.</p>
      </div>
      <div class="step-card">
        <div class="step-number">3</div>
        <h3 class="step-title">Candidate-se</h3>
        <p class="step-text">Sem cadastro, sem burocracia. Clique na vaga e candidate-se diretamente no site da empresa. O Mondywork é 100% gratuito e não exige criação de conta.</p>
      </div>
      <div class="step-card">
        <div class="step-number">4</div>
        <h3 class="step-title">Receba alertas</h3>
        <p class="step-text">Cadastre seu e-mail na newsletter e receba as melhores oportunidades diretamente na sua caixa de entrada, filtradas pela sua área de interesse.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Guias de Carreira</h2>
    </div>
    <div class="guide-grid">
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
        <h3>Guia de Carreira em Tecnologia</h3>
        <p>Planejamento, habilidades, entrevistas e crescimento profissional em TI, Ciência de Dados, DevOps e Produto.</p>
        <p class="guide-card-desc">O guia mais completo para profissionais de tecnologia. Aborda desde a escolha da área de atuação (desenvolvimento, dados, infraestrutura, QA) até a preparação para entrevistas técnicas e planejamento de carreira a longo prazo. Inclui dicas de portfólio, LinkedIn e desenvolvimento de soft skills essenciais para o mercado de tecnologia.</p>
        <a href="/guia-de-carreira.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <h3>Guia de Carreira em Design</h3>
        <p>UX/UI, Design Gráfico, Design de Produto. Portfólio, ferramentas, entrevistas e crescimento na área de Design.</p>
        <p class="guide-card-desc">Um guia completo para profissionais de design que desejam se destacar no mercado. Aborda UX/UI, Design Gráfico, Design de Produto e muito mais. Inclui dicas de construção de portfólio, ferramentas essenciais, preparação para entrevistas de design e estratégias de crescimento profissional na área criativa.</p>
        <a href="/guia-de-carreira-design.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#0891b2,#22d3ee)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/><path d="M12 6v12"/></svg>
        </div>
        <h3>Guia de Carreira em Marketing</h3>
        <p>SEO, Mídia Paga, Growth, Marketing de Conteúdo. Certificações, ferramentas e estratégias para crescer no Marketing Digital.</p>
        <p class="guide-card-desc">O guia definitivo para profissionais de marketing digital. Cobre desde os fundamentos de SEO e Marketing de Conteúdo até estratégias avançadas de Mídia Paga e Growth. Inclui certificações recomendadas, ferramentas indispensáveis e um plano de carreira para cada etapa da sua jornada no marketing.</p>
        <a href="/guia-de-carreira-marketing.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#059669,#34d399)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3>Guia de Carreira em Finanças</h3>
        <p>Mercado financeiro, investimentos, finanças corporativas, certificações e estratégias para crescer na área financeira.</p>
        <p class="guide-card-desc">Aprenda tudo sobre carreira no mercado financeiro. Do currículo ideal às certificações mais valorizadas (CFA, CPA, ANCORD), passando por dicas de entrevistas em bancos e funds. Aborda finanças corporativas, investimentos, private equity e as tendências do setor financeiro no Brasil.</p>
        <a href="/guia-de-carreira-financas.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#e11d48,#fb7185)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h3>Guia de Carreira em Comunicacao</h3>
        <p>Jornalismo, RP, Comunicacao Corporativa, Marketing de Conteudo e Producao Multimidia.</p>
        <p class="guide-card-desc">Um guia completo para profissionais de comunicacao. Aborda desde Jornalismo e Comunicacao Corporativa ate Marketing de Conteudo e Producao Multimidia. Inclui dicas de portfolio, ferramentas essenciais, preparacao para entrevistas e estrategias de crescimento na area.</p>
        <a href="/guia-de-carreira-comunicacao.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#0891b2,#22d3ee)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <h3>Guia de Carreira em Administracao</h3>
        <p>Gestao de Empresas, RH, Logistica, Consultoria, Gestao de Projetos e Empreendedorismo.</p>
        <p class="guide-card-desc">Aprenda tudo sobre carreira em administracao. Do currículo ideal as certificacoes mais valorizadas (PMP, CPA, Six Sigma), passando por dicas de entrevistas em consultorias e grandes empresas. Aborda gestao de pessoas, financas, operacoes e estrategia.</p>
        <a href="/guia-de-carreira-administracao.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#d97706,#fbbf24)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
        </div>
        <h3>Guia de Carreira em Dados</h3>
        <p>Ciencia de Dados, Engenharia de Dados, BI, Machine Learning e IA. Da formacao ao mercado.</p>
        <p class="guide-card-desc">O guia completo para profissionais de dados. Aborda Ciencia de Dados, Engenharia de Dados, Analise de Dados, Business Intelligence, Machine Learning e Inteligencia Artificial. Inclui roadmap de aprendizagem, ferramentas essenciais (Python, SQL, Spark), portfolios de dados, preparacao para entrevistas tecnicas e plano de carreira na area de dados.</p>
        <a href="/guia-de-carreira-dados.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
      <div class="guide-card guide-card--primary">
        <div class="guide-card-icon" style="background:linear-gradient(135deg,#059669,#34d399)">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3>Guia de Carreira em Produto</h3>
        <p>Product Management, Product Ownership, Agile, Scrum e OKRs. Da estrategia a execucao.</p>
        <p class="guide-card-desc">O guia essencial para profissionais de produto. Aborda Product Management, Product Ownership, metodologias Ageis (Scrum, Kanban), OKRs, descoberta de produto, priorizacao, analise de metricas e lideranca de produto. Inclui certificacoes (CSPO, PSPO), ferramentas (Jira, Notion, Amplitude) e plano de carreira de PM a CPO.</p>
        <a href="/guia-de-carreira-produto.php" class="guide-card-link">Ler guia completo &rarr;</a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Áreas que cobrimos</h2>
    </div>
    <div class="areas-grid">
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#4b41e1,#7c73f0)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        </div>
        <h3 class="area-title">Tecnologia</h3>
        <p class="area-text">Desenvolvimento de software, ciência de dados, inteligência artificial, infraestrutura cloud, DevOps, cibersegurança e engenharia. A área com maior demanda e salários mais competitivos do mercado.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </div>
        <h3 class="area-title">Design</h3>
        <p class="area-text">UX/UI, Product Design, Design Gráfico, Motion Design e Design Thinking. Profissionais criativos que transformam ideias em experiências digitais memoráveis e funcionais.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#0891b2,#22d3ee)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/><path d="M12 6v12"/></svg>
        </div>
        <h3 class="area-title">Marketing</h3>
        <p class="area-text">Marketing Digital, SEO, Mídia Paga, Growth Hacking, Marketing de Conteúdo, Social Media e Branding. Estratégias baseadas em dados para impulsionar resultados e construir marcas.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#059669,#34d399)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3 class="area-title">Produto</h3>
        <p class="area-text">Product Management, Product Ownership, Agile, Scrum e OKRs. Profissionais que conectam estratégia de negócios com execução técnica para entregar produtos digitais de alto valor.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#e11d48,#fb7185)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h3 class="area-title">Comunicacao</h3>
        <p class="area-text">Jornalismo, Comunicacao Corporativa, Relacoes Publicas, Marketing de Conteudo, Producao Multimidia e Social Media. Profissionais que dominam a arte de contar historias e construir reputacao em um mundo multicanal.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#0891b2,#22d3ee)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
        </div>
        <h3 class="area-title">Administracao</h3>
        <p class="area-text">Gestao de Pessoas, Financas, Operacoes, Logistica, Consultoria e Gestao de Projetos. Profissionais que planejam, organizam e lideram recursos para alcancar resultados estrategicos.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#059669,#34d399)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3 class="area-title">Financas</h3>
        <p class="area-text">Mercado Financeiro, Investimentos, Financas Corporativas, Contabilidade e Compliance. Profissionais que gerem recursos, analisam riscos e impulsionam a saude financeira das organizacoes.</p>
      </div>
      <div class="area-card">
        <div class="area-icon" style="background:linear-gradient(135deg,#d97706,#fbbf24)">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
        </div>
        <h3 class="area-title">Dados</h3>
        <p class="area-text">Ciencia de Dados, Engenharia de Dados, Analise de Dados, Business Intelligence, Machine Learning e IA. Profissionais que transformam dados brutos em insights estrategicos que orientam decisoes de negocios.</p>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Quem usa recomenda</h2>
    </div>
    <div class="testimonial-grid">
      <div class="testimonial-card">
        <img src="/img/testimonials/nery.jpeg" alt="Nery Neto" class="testimonial-avatar">
        <div class="testimonial-body">
          <p class="testimonial-text">"Sem dúvidas é um dos melhores sites de vaga que já acessei. Não pede cadastro e ainda é 100% gratuito."</p>
          <div class="testimonial-author">
            <div class="testimonial-author-row"><strong>Nery Neto</strong>
            <a href="https://www.linkedin.com/in/nery-marques/" target="_blank" class="testimonial-linkedin" aria-label="LinkedIn">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a></div>
            <span class="testimonial-role">Engenheiro de Visão Computacional</span>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <img src="/img/testimonials/barbara.jpeg" alt="Barbara Abrita" class="testimonial-avatar">
        <div class="testimonial-body">
          <p class="testimonial-text">"Tem excelentes oportunidades nas áreas de inteligência de dados e finanças."</p>
          <div class="testimonial-author">
            <div class="testimonial-author-row"><strong>Barbara Abrita</strong>
            <a href="https://www.linkedin.com/in/b%C3%A1rbara-abrita-4672201b4/" target="_blank" class="testimonial-linkedin" aria-label="LinkedIn">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a></div>
            <span class="testimonial-role">Analista de Dados em Finanças</span>
          </div>
        </div>
      </div>
      <div class="testimonial-card">
        <img src="/img/testimonials/gabriel.jpeg" alt="Gabriel Marques" class="testimonial-avatar">
        <div class="testimonial-body">
          <p class="testimonial-text">"Mondywork foi feito pensando em facilitar a vida de quem está buscando uma nova oportunidade e sua agilidade mostra isso."</p>
          <div class="testimonial-author">
            <div class="testimonial-author-row"><strong>Gabriel Marques</strong>
            <a href="https://www.linkedin.com/in/gabri3lmarques/" target="_blank" class="testimonial-linkedin" aria-label="LinkedIn">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a></div>
            <span class="testimonial-role">Desenvolvedor Web</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Perguntas Frequentes</h2>
    </div>
    <div class="faq-list">
      <details class="faq-item">
        <summary class="faq-question">O que é o Mondywork?</summary>
        <div class="faq-answer">
          <p>É um portal que conecta profissionais às melhores oportunidades de trabalho em Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados e Produto. Combinamos um blog com conteúdo original sobre carreira com uma plataforma de vagas que reúne diariamente centenas de oportunidades em todo o Brasil.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Precisa criar conta ou cadastrar currículo?</summary>
        <div class="faq-answer">
          <p>Não. O Mondywork é 100% gratuito e não exige cadastro. Você pode buscar vagas, ler o blog e acessar todos os conteúdos sem criar conta alguma.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">De onde vêm as vagas?</summary>
        <div class="faq-answer">
          <p>Coletamos vagas de centenas de empresas através de parcerias com as principais plataformas de recrutamento do mercado (InHire, Ashby e Greenhouse). Isso nos permite oferecer uma experiência unificada de busca de emprego.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Como as vagas são categorizadas?</summary>
        <div class="faq-answer">
          <p>Classificamos automaticamente as oportunidades por área de atuação (Tecnologia, Design, Marketing, Comunicacao, Administracao, Financas, Dados, Produto) para facilitar sua busca. Você também pode pesquisar por cargo, palavra-chave ou habilidade.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Com que frequência as vagas são atualizadas?</summary>
        <div class="faq-answer">
          <p>Diariamente. Novas oportunidades são adicionadas todos os dias, e vagas que não estão mais disponíveis são removidas automaticamente.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Sou empresa. Como divulgar minhas vagas?</summary>
        <div class="faq-answer">
          <p>Entre em contato conosco através da página de <a href="/contato.php">Contato</a>. Estamos abertos a novas parcerias para conectar talentos às melhores oportunidades.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">O conteúdo do blog é gratuito?</summary>
        <div class="faq-answer">
          <p>Sim! Todo o conteúdo do blog Mondywork é 100% gratuito. Acreditamos que informação de qualidade sobre carreira e mercado de trabalho deve ser acessível a todos os profissionais.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Como recebo notificações de novas vagas?</summary>
        <div class="faq-answer">
          <p>Cadastre seu nome, e-mail e área de interesse no formulário de newsletter disponível na página de vagas. Passaremos a enviar as melhores oportunidades diretamente para sua caixa de entrada.</p>
        </div>
      </details>
      <details class="faq-item">
        <summary class="faq-question">Posso contribuir com o blog?</summary>
        <div class="faq-answer">
          <p>Sim! Aceitamos contribuições de profissionais que desejam compartilhar conhecimento sobre carreira, mercado de trabalho e desenvolvimento profissional. Entre em contato pela página de Contato para saber mais.</p>
        </div>
      </details>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/contato.php">Contato</a>
      <a class="footer-link" href="/sobre.php">Sobre</a>
      <a class="footer-link" href="/guia-de-carreira.php">Guia de Tecnologia</a>
      <a class="footer-link" href="/guia-de-carreira-design.php">Guia de Design</a>
      <a class="footer-link" href="/guia-de-carreira-marketing.php">Guia de Marketing</a>
      <a class="footer-link" href="/guia-de-carreira-comunicacao.php">Guia de Comunicacao</a>
      <a class="footer-link" href="/guia-de-carreira-administracao.php">Guia de Administracao</a>
      <a class="footer-link" href="/guia-de-carreira-financas.php">Guia de Finanças</a>
      <a class="footer-link" href="/privacidade.php">Privacidade</a>
      <a class="footer-link" href="/termos-de-uso.php">Termos</a>
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
