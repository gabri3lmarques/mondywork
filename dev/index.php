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
<link rel="stylesheet" href="../css/style.css?v=1.0.8">
<link rel="icon" href="../img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="../img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="../img/favicon/apple-touch-icon.png">
<style>
.job-card-featured {
  background: linear-gradient(135deg, #fefcf0 0%, #fffdf7 50%, #ffffff 100%);
  border-radius: 0.75rem;
  padding: 32px;
  box-shadow: 0 8px 20px -6px rgba(180, 150, 60, 0.12), 0 4px 6px -1px rgba(0,0,0,0.06);
  border: 1px solid #e8dcc8;
  transition: box-shadow 0.3s, border-color 0.3s, transform 0.2s;
  position: relative;
}

.job-card-featured:hover {
  box-shadow: 0 14px 28px -8px rgba(180, 150, 60, 0.18), 0 4px 12px -2px rgba(0,0,0,0.08);
  border-color: #d4c4a8;
  transform: translateY(-2px);
}

.job-card-featured::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: linear-gradient(180deg, #e8b84b, #d4a43a);
  border-radius: 4px 0 0 4px;
}

.job-card-featured .badge-featured {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: linear-gradient(135deg, #f0d78a, #e8c56a);
  color: #5c4510;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  padding: 4px 12px;
  border-radius: 9999px;
  border: 1px solid rgba(200, 170, 70, 0.3);
  margin-bottom: 12px;
}

.job-card-featured .job-card-title {
  font-size: 24px;
  line-height: 32px;
  font-weight: 600;
  color: #0b1c30;
}

.job-card-featured .job-card-company {
  font-size: 14px;
  line-height: 20px;
  font-weight: 500;
  color: #45464d;
  margin-top: 4px;
}

.job-card-featured .job-card-info {
  margin-top: 16px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.job-card-featured .job-card-info-text {
  font-size: 12px;
  line-height: 16px;
  font-weight: 600;
  color: #45464d;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.job-card-featured .job-card-info-text svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.job-card-featured .job-card-resumo {
  font-size: 16px;
  line-height: 24px;
  color: #45464d;
  margin-top: 16px;
}

.job-card-featured .job-card-footer {
  border-top: 1px solid #e8dcc8;
  padding-top: 16px;
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.job-card-featured .job-card-btn {
  font-size: 14px;
  line-height: 20px;
  font-weight: 500;
  color: #4b41e1;
  background: transparent;
  border: 1px solid #4b41e1;
  padding: 8px 24px;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.3s;
  white-space: nowrap;
}

.job-card-featured .job-card-btn:hover { background-color: #eff4ff; }

.job-card-featured .featured-price {
  font-size: 13px;
  font-weight: 600;
  color: #8a7030;
  display: flex;
  align-items: center;
  gap: 4px;
}

.job-card-featured .featured-price svg {
  width: 14px;
  height: 14px;
}

.section-featured {
  padding: 0 16px;
  max-width: 1280px;
  margin: 0 auto 48px;
}

@media (min-width: 768px) {
  .section-featured { padding: 0 48px; }
}

.section-featured .section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 24px;
}

.section-featured .section-subtitle {
  font-size: 14px;
  color: #8a7030;
  font-weight: 500;
}

.featured-list {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
</style>
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

  <section class="section-featured">
    <div class="section-header">
      <div>
        <h2 class="section-title" style="font-size:30px;font-weight:600;letter-spacing:-0.01em;color:#0b1c30">Vagas em Destaque</h2>
        <p class="section-subtitle">Posicione sua empresa aqui &mdash; <a href="mailto:hello@mondywork.com" style="color:#4b41e1;text-decoration:underline">hello@mondywork.com</a></p>
      </div>
    </div>
    <div class="featured-list">
      <article class="job-card-featured">
        <span class="badge-featured">✦ Destaque</span>
        <div>
          <h3 class="job-card-title">Senior Software Engineer</h3>
          <p class="job-card-company">Empresa Exemplo • <span style="color:#8a7030;font-weight:600">Patrocinado</span></p>
        </div>
        <div class="job-card-info">
          <span class="badge badge-remote">Remoto</span>
          <span class="job-card-info-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg>
            Brasil
          </span>
        </div>
        <p class="job-card-resumo line-clamp-2">Oportunidade como Senior Software Engineer em modelo remoto. Stack moderna, time multicultural e benefícios competitivos.</p>
        <div class="job-card-footer">
          <button class="job-card-btn" onclick="alert('Vaga de exemplo — anuncie a sua aqui!')">Ver Detalhes</button>
          <span class="featured-price">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Vaga Patrocinada
          </span>
        </div>
      </article>
      <article class="job-card-featured">
        <span class="badge-featured">✦ Destaque</span>
        <div>
          <h3 class="job-card-title">Product Designer (UX/UI)</h3>
          <p class="job-card-company">Outra Empresa • <span style="color:#8a7030;font-weight:600">Patrocinado</span></p>
        </div>
        <div class="job-card-info">
          <span class="badge badge-hybrid">Híbrido</span>
          <span class="job-card-info-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg>
            São Paulo, SP
          </span>
        </div>
        <p class="job-card-resumo line-clamp-2">Buscamos um Product Designer para criar experiências incríveis. Design System, prototipação avançada e pesquisa com usuários.</p>
        <div class="job-card-footer">
          <button class="job-card-btn" onclick="alert('Vaga de exemplo — anuncie a sua aqui!')">Ver Detalhes</button>
          <span class="featured-price">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Vaga Patrocinada
          </span>
        </div>
      </article>
    </div>
  </section>

  <div class="support-banner">
    <strong class="support-banner-title">Ajude a manter o site</strong>
    <p class="support-banner-text">Colabore com a sustentabilidade do projeto e clique em uma propaganda. Assim você nos ajuda a manter esse projeto.</p>
  </div>
  <div style="text-align:center;margin:24px 0">
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
</div>

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
    <div style="text-align:center;margin:12px 0;padding:0 16px">
<script>
  atOptions = {
    'key' : '4ebf9218fca889c045c30ab6b62d4769',
    'format' : 'iframe',
    'height' : 50,
    'width' : 320,
    'params' : {}
  };
</script>
<script src="https://www.highperformanceformat.com/4ebf9218fca889c045c30ab6b62d4769/invoke.js"></script>
    </div>
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
