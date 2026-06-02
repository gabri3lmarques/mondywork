<?php
session_start();

define('DEV_PASSWORD', 'dev123');

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === DEV_PASSWORD) {
        $_SESSION['dev_auth'] = true;
    } else {
        $error = 'Senha incorreta.';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['dev_auth']);
    session_destroy();
    header('Location: .');
    exit;
}

$authenticated = !empty($_SESSION['dev_auth']);

if (!$authenticated):
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mondywork DEV — Acesso Restrito</title>
<meta name="robots" content="noindex, nofollow">
<style>
  *{margin:0;padding:0;box-sizing:border-box}
  body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#1a1a2e;color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
  .login{background:#16213e;padding:40px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.3);width:100%;max-width:400px;text-align:center}
  .login h1{font-size:24px;margin-bottom:4px;color:#fff}
  .login .dev-label{font-size:13px;color:#ffd43b;font-weight:600;margin-bottom:24px;display:block}
  .login p{color:#a0a0b8;margin-bottom:24px;font-size:14px;line-height:1.5}
  .login form{display:flex;flex-direction:column;gap:12px}
  .login input[type="password"]{padding:14px 16px;border-radius:10px;border:1px solid #2a2a4a;background:#0f3460;color:#fff;font-size:16px;outline:none;transition:border .2s}
  .login input[type="password"]:focus{border-color:#4b41e1}
  .login button{padding:14px;border-radius:10px;border:none;background:#4b41e1;color:#fff;font-size:16px;font-weight:600;cursor:pointer;transition:background .2s}
  .login button:hover{background:#3a32b8}
  .login .error{background:#5c1a1a;color:#ff6b6b;padding:10px 14px;border-radius:8px;font-size:14px;margin-bottom:12px}
</style>
</head>
<body>
  <div class="login">
    <h1>Mondywork <span style="color:#ffd43b">DEV</span></h1>
    <span class="dev-label">Ambiente de Desenvolvimento</span>
    <p>Digite a senha para acessar.</p>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="password" name="password" placeholder="Senha" autofocus required>
      <button type="submit">Acessar</button>
    </form>
  </div>
</body>
</html>
<?php
exit;
endif;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Mondywork DEV | Vagas de Tecnologia, Design e Marketing</title>
<meta name="description" content="[DEV] Ambiente de desenvolvimento para testar novas funcionalidades.">
<meta name="robots" content="noindex, nofollow">
<link rel="stylesheet" href="../css/style.css?v=1.0.5">
<link rel="icon" href="../img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="../img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="../img/favicon/apple-touch-icon.png">
</head>
<body>

<div class="dev-banner" style="background:#ffd43b;color:#1a1a2e;text-align:center;padding:6px 16px;font-size:14px;font-weight:600;position:sticky;top:0;z-index:999">
  ⚡ Ambiente de Desenvolvimento &mdash; <a href="?logout" style="color:#1a1a2e;text-decoration:underline">Sair</a>
</div>

<nav class="nav">
  <div class="nav-inner">
    <a class="nav-logo" href="#">Mondywork <span style="font-size:12px;font-weight:400;color:#ffd43b;margin-left:6px">DEV</span></a>
    <div class="nav-links">
      <a class="nav-link" href="../sobre.html">Sobre</a>
      <a class="nav-link" href="../contato.html">Contato</a>
      <a class="nav-link" href="../privacidade.html">Privacidade</a>
      <a class="nav-link" href="../termos-de-uso.html">Termos</a>
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
  <a class="nav-link" href="../sobre.html">Sobre</a>
  <a class="nav-link" href="../contato.html">Contato</a>
  <a class="nav-link" href="../privacidade.html">Privacidade</a>
  <a class="nav-link" href="../termos-de-uso.html">Termos</a>
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
        <div id="search-correction" class="search-correction hidden"></div>
        <div id="vagas-total" class="search-info"></div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Vagas Recentes</h2>
    </div>
    <div id="results-info" class="results-info"></div>
    <div class="job-grid">
      <div class="job-list" id="vagas-container"></div>
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
    <span class="footer-logo">Mondywork DEV</span>
    <div class="footer-links">
      <a class="footer-link" href="../contato.html">Contato</a>
      <a class="footer-link" href="../sobre.html">Sobre</a>
      <a class="footer-link" href="../privacidade.html">Privacidade</a>
      <a class="footer-link" href="../termos-de-uso.html">Termos</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork DEV &mdash; ambiente de desenvolvimento.</p>
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
  <p class="cookie-text">Utilizamos cookies para melhorar sua experiência e analisar o tráfego do site. Ao continuar navegando, você concorda com nossa <a href="../privacidade.html">Política de Privacidade</a>.</p>
  <button id="cookie-accept" class="cookie-btn">Aceitar</button>
</div>

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

<script src="../js/app-dev.js?v=1.0.0"></script>
</body>
</html>
