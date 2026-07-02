<?php
$configFile = file_exists(__DIR__ . '/../config.local.php') ? __DIR__ . '/../config.local.php' : __DIR__ . '/../config.php';
$config = require $configFile;

$filterModelo = isset($_GET['modelo']) ? trim($_GET['modelo']) : '';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/../lib/Database.php';
    setupSchema($pdo);

    $where = "WHERE v.status = 'ativa' AND v.origem = 'nacional'";
    $params = [];
    if ($filterModelo) {
        $where .= " AND v.modelo_trabalho = :modelo";
        $params[':modelo'] = $filterModelo;
    }

    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM vagas v {$where}");
    foreach ($params as $k => $v) $stmtCount->bindValue($k, $v, PDO::PARAM_STR);
    $stmtCount->execute();
    $total = (int)$stmtCount->fetchColumn();

    $campos = "v.vaga_id_externo, v.titulo, v.empresa, v.localizacao, v.modelo_trabalho, v.url_vaga, v.resumo, DATE_FORMAT(v.publicado_em, '%d/%m/%Y') as publicado_em";
    $stmt = $pdo->prepare("SELECT {$campos} FROM vagas v {$where} ORDER BY v.publicado_em DESC, v.data_coleta DESC LIMIT 50");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($vagas as &$v) { $v['titulo'] = capitalizeTitle($v['titulo']); }
    unset($v);
    $hasMore = $total > 50;
} catch (Exception $e) {
    $vagas = [];
    $total = 0;
    $hasMore = false;
}

function badgeClass($modelo) {
    if (!$modelo) return '';
    $m = mb_strtolower($modelo);
    if ($m === 'remoto' || $m === 'remote') return 'badge badge-remote';
    if ($m === 'híbrido' || $m === 'hibrido' || $m === 'hybrid') return 'badge badge-hybrid';
    return 'badge badge-onsite';
}

function formatModelo($modelo) {
    $map = ['Remote' => 'Remoto', 'Hybrid' => 'Híbrido', 'On-site' => 'Presencial'];
    return $map[$modelo] ?? $modelo;
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function capitalizeTitle($str) {
    return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Vagas de Tecnologia, Design e Marketing | Mondywork</title>
<meta name="description" content="Encontre as melhores vagas de trabalho remoto e presencial nas áreas de Tecnologia, Design, Marketing e Produto. Oportunidades atualizadas diariamente em todo o Brasil.">
<?php if ($filterModelo): ?><meta name="robots" content="noindex,follow"><?php endif; ?>
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com/vagas/">
<link rel="alternate" hreflang="en" href="https://mondywork.com/usa/">
<link rel="canonical" href="https://mondywork.com/vagas/">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com/vagas/">
<meta property="og:title" content="Vagas de Tecnologia, Design e Marketing | Mondywork">
<meta property="og:description" content="Encontre as melhores vagas de trabalho remoto e presencial nas áreas de Tecnologia, Design, Marketing e Produto em todo o Brasil.">
<meta property="og:image" content="https://mondywork.com/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com/vagas/">
<meta property="twitter:title" content="Vagas de Tecnologia, Design e Marketing | Mondywork">
<meta property="twitter:description" content="Encontre as melhores vagas de trabalho remoto e presencial nas áreas de Tecnologia, Design, Marketing e Produto em todo o Brasil.">
<meta property="twitter:image" content="https://mondywork.com/img/og-image.jpg">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "Mondywork",
  "url": "https://mondywork.com/vagas/",
  "description": "Portal de vagas de tecnologia, design, marketing e produto.",
  "inLanguage": "pt-BR"
}
</script>
<link rel="stylesheet" href="/css/style.css?v=1.6.0">
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
      <a class="nav-link active" href="/vagas/">Vagas</a>
      <a class="nav-link" href="/">Blog</a>
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
  <a class="nav-link active" href="/vagas/">Vagas</a>
  <a class="nav-link" href="/">Blog</a>
  <a class="nav-link" href="/sobre.html">Sobre</a>
  <a class="nav-link" href="/contato.html">Contato</a>
  <a class="nav-link" href="/usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
  <a class="nav-icon-mobile" aria-label="X (Twitter)" href="https://x.com/mondywork" target="_blank">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
  </a>
  <a class="nav-icon-mobile" aria-label="LinkedIn" href="https://www.linkedin.com/company/mondywork/" target="_blank">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
  </a>
</div>

<main class="main-content">
  <section class="hero">
    <div class="hero-decor hero-decor-1"></div>
    <div class="hero-decor hero-decor-2"></div>
    <div class="hero-content">
      <h1 class="hero-title">Sua próxima vaga está aqui</h1>
      <p class="hero-subtitle">Tecnologia, Design, Marketing e Produto. Oportunidades atualizadas diariamente em todo o Brasil.</p>
      <div class="hero-search">
        <div class="glass-panel">
          <div class="search-input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input id="search" class="search-input" placeholder="Cargo, palavra-chave ou empresa" type="text">
          </div>
        </div>
        <div id="search-loading" class="search-loading hidden">
          <svg class="loading-icon-sm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
          <span>Buscando...</span>
        </div>
        <div class="search-modes">
          <label class="search-mode-label">
            <input type="radio" name="modo" value="titulo" checked>
            Buscar por cargo
          </label>
          <label class="search-mode-label">
            <input type="radio" name="modo" value="descricao">
            Buscar por habilidade
          </label>
        </div>
        <div class="hero-filters" id="hero-filters">
          <label class="filter-checkbox">
            <input type="checkbox" id="filter-remoto"<?= $filterModelo === 'Remote' ? ' checked' : '' ?>>
            <span>Apenas vagas remotas</span>
          </label>
        </div>
        <div id="search-correction" class="search-correction hidden"></div>
        <div id="vagas-total" class="search-info"><?= $total ?> vagas ativas</div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Vagas Recentes</h2>
    </div>
    <div id="results-info" class="results-info"></div>
    <div class="job-grid">
      <div class="job-list" id="vagas-container">
<?php foreach ($vagas as $v):
    $modelo = $v['modelo_trabalho'] ? formatModelo($v['modelo_trabalho']) : null;
    $local = $v['localizacao'] ?: 'Remoto';
    $resumo = $v['resumo'] ?: 'Oportunidade como ' . $v['titulo'] . ($modelo ? ' em modelo ' . mb_strtolower($modelo) : '') . '.';
    $badge = $modelo ? '<span class="' . badgeClass($v['modelo_trabalho']) . '">' . esc($modelo) . '</span>' : '';
?>
        <article class="job-card" data-vaga-id="<?= esc($v['vaga_id_externo']) ?>">
          <div>
            <h3 class="job-card-title"><a href="/vaga/<?= esc($v['vaga_id_externo']) ?>" class="job-card-link"><?= esc($v['titulo']) ?></a></h3>
            <p class="job-card-company"><?= esc($v['empresa']) ?></p>
          </div>
          <div class="job-card-info">
            <?= $badge ?>
            <span class="job-card-info-text"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg><?= esc($local) ?></span>
<?php if ($v['publicado_em']): ?>
            <span class="job-card-info-text job-card-date"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?= esc($v['publicado_em']) ?></span>
<?php endif; ?>
          </div>
          <p class="job-card-resumo line-clamp-2"><?= esc($resumo) ?></p>
          <div class="job-card-footer">
            <a href="/vaga/<?= esc($v['vaga_id_externo']) ?>" class="job-card-btn">Ver Detalhes</a>
          </div>
        </article>
<?php endforeach; ?>
      </div>
      <aside class="sidebar">
        <div class="sidebar-card">
          <div class="sidebar-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
          </div>
          <h3 class="sidebar-title">Receba vagas antes de todo mundo</h3>
          <p class="sidebar-text">Cadastre seu nome e e-mail para receber as melhores oportunidades diretamente na sua caixa de entrada.</p>
          <form class="sidebar-form" id="newsletter-form">
            <input class="sidebar-input" placeholder="Nome" type="text">
            <input class="sidebar-input" placeholder="E-mail" type="email">
            <select class="sidebar-select" id="newsletter-area" required>
              <option value="">Área de interesse</option>
              <option value="dev">Desenvolvimento / Software</option>
              <option value="engenharia">Engenharia</option>
              <option value="dados">Dados / BI</option>
              <option value="ia">IA / Machine Learning</option>
              <option value="design">UX / UI / Product Design</option>
              <option value="marketing">Marketing Digital / Growth</option>
              <option value="social-media">Social Media / Conteúdo</option>
              <option value="produto">Produto (PM/PO)</option>
              <option value="agile">Agilidade / Scrum</option>
              <option value="gestao">Gestão / Projetos</option>
              <option value="vendas">Comercial / Vendas</option>
              <option value="customer-success">Customer Success / CX</option>
              <option value="suporte">Suporte Técnico / Help Desk</option>
              <option value="qa">QA / Testes</option>
              <option value="infra">Infraestrutura / Cloud / DevOps</option>
            </select>
            <button class="sidebar-btn" type="submit">Cadastrar Agora</button>
          </form>
        </div>
      </aside>
    </div>
    <div id="loading" class="loading hidden">
      <svg class="loading-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
      <span>Carregando...</span>
    </div>
    <div id="sentinel" style="height:1px"></div>
  </section>
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
      <a class="footer-link" href="/guia-de-carreira-dados.html">Guia de Dados</a>
      <a class="footer-link" href="/guia-de-carreira-produto.html">Guia de Produto</a>
      <a class="footer-link" href="/guia-de-carreira-financas.html">Guia de Finanças</a>
      <a class="footer-link" href="/privacidade.html">Privacidade</a>
      <a class="footer-link" href="/termos-de-uso.html">Termos</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. Todos os direitos reservados.</p>
  </div>
</footer>

<button id="back-to-top" class="back-to-top hidden" aria-label="Voltar ao topo">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<div id="modal-overlay" class="modal-overlay hidden">
  <div class="modal-content" role="dialog" aria-modal="true">
    <div class="modal-header">
      <div>
        <h2 id="modal-title" class="modal-title"></h2>
        <p id="modal-subtitle" class="modal-subtitle"></p>
      </div>
      <button id="modal-close" class="modal-close" aria-label="Fechar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div id="modal-body" class="modal-body"></div>
    <div class="modal-footer" id="modal-footer">
      <a id="modal-apply" href="#" target="_blank" class="modal-btn">Aplicar na Vaga</a>
    </div>
  </div>
</div>

<div id="cookie-banner" class="cookie-banner">
  <p class="cookie-text">Utilizamos cookies para melhorar sua experiência e analisar o tráfego do site. Ao continuar navegando, você concorda com nossa <a href="/privacidade.html">Política de Privacidade</a>.</p>
  <button id="cookie-accept" class="cookie-btn">Aceitar</button>
</div>

<script id="vagas-data" type="application/json"><?= json_encode([
    'vagas' => $vagas,
    'total' => $total,
    'has_more' => $hasMore,
    'query' => '',
    'modo' => 'titulo',
    'modelo' => $filterModelo,
], JSON_UNESCAPED_UNICODE) ?></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (!localStorage.getItem('cookie_consent')) {
    document.getElementById('cookie-banner').classList.add('visible');
  }
  document.getElementById('cookie-accept').addEventListener('click', function() {
    localStorage.setItem('cookie_consent', 'true');
    document.getElementById('cookie-banner').classList.remove('visible');
  });


});
</script>

<script src="/js/app.js?v=1.6.0"></script>
</body>
</html>
