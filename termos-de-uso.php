<?php
$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/BlogHelper.php';
try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    setupSchema($pdo);
    $blogPosts = getBlogPosts($pdo);
} catch (Exception $e) { $blogPosts = []; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Termos de Uso | Mondywork</title>
<meta name="description" content="Termos de Uso do Mondywork. Ao utilizar nosso site, você concorda com estes termos.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com/termos-de-uso.php">
<meta property="og:title" content="Termos de Uso | Mondywork">
<meta property="og:description" content="Termos de Uso do Mondywork. Ao utilizar nosso site, você concorda com estes termos.">
<meta property="og:image" content="https://mondywork.com/img/og-image.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com/termos-de-uso.php">
<meta property="twitter:title" content="Termos de Uso | Mondywork">
<meta property="twitter:description" content="Termos de Uso do Mondywork. Ao utilizar nosso site, você concorda com estes termos.">
<meta property="twitter:image" content="https://mondywork.com/img/og-image.jpg">
<link rel="stylesheet" href="/css/style.css?v=1.7.5">
<link rel="icon" href="./img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="./img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="./img/favicon/apple-touch-icon.png">
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
      <a class="nav-link" href="/">Blog</a>
      <a class="nav-link nav-btn" href="/vagas/">Vagas</a>
      <a class="nav-link" href="sobre.php">Sobre</a>
      <a class="nav-link" href="contato.php">Contato</a>
      <a class="nav-link" href="./usa/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA & worldwide</a>
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
  <a class="nav-link" href="/">Blog</a>
  <a class="nav-link nav-btn" href="/vagas/">Vagas</a>
      <a class="nav-link" href="sobre.php">Sobre</a>
  <a class="nav-link" href="contato.php">Contato</a>
  <a class="nav-link" href="./usa/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#fff"/><rect y="0" width="18" height="1.09" fill="#b22234"/><rect y="2.18" width="18" height="1.09" fill="#b22234"/><rect y="4.36" width="18" height="1.09" fill="#b22234"/><rect y="6.55" width="18" height="1.09" fill="#b22234"/><rect y="8.73" width="18" height="1.09" fill="#b22234"/><rect y="10.91" width="18" height="1.09" fill="#b22234"/><rect width="7.2" height="6.55" fill="#3c3b6e"/></svg>Jobs in USA and worldwide</a>
  <a class="nav-icon-mobile" aria-label="X (Twitter)" href="https://x.com/mondywork" target="_blank">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
  </a>
  <a class="nav-icon-mobile" aria-label="LinkedIn" href="https://www.linkedin.com/company/mondywork/" target="_blank">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
  </a>
</div>

<main class="main-content">
  <section class="legal-section">
    <div class="legal-container">
      <h1 class="legal-title">Termos de Uso</h1>
      <p class="legal-update">Última atualização: Maio de 2026</p>

      <p>Bem-vindo ao Mondywork. Ao acessar e utilizar nosso site, você concorda com os termos e condições descritos abaixo. Caso não concorde com algum destes termos, recomendamos que não utilize nossos serviços.</p>

      <h2>1. Serviço</h2>
      <p>O Mondywork é um agregador de vagas de emprego que reúne oportunidades de trabalho de diversas empresas e plataformas de recrutamento. Nosso papel é facilitar a descoberta de vagas, mas não somos responsáveis pelo processo seletivo, contratação ou qualquer relação entre candidatos e empresas anunciantes.</p>

      <h2>2. Conteúdo das Vagas</h2>
      <p>As vagas exibidas em nossa plataforma são obtidas automaticamente de fontes públicas e parceiras (InHire, Ashby, Greenhouse e sites de carreiras de empresas). As informações sobre salário, benefícios, requisitos e descrições são de responsabilidade exclusiva das empresas anunciantes. Recomendamos verificar os detalhes diretamente no site original da vaga antes de se candidatar.</p>

      <h2>3. Newsletter</h2>
      <p>Ao se cadastrar em nossa newsletter, você concorda em receber e-mails com vagas de emprego e oportunidades. Você pode descadastrar-se a qualquer momento através do link presente em cada e-mail ou entrando em contato conosco. Seus dados de cadastro (nome, e-mail e área de interesse) serão armazenados e utilizados exclusivamente para o envio da newsletter, conforme descrito em nossa Política de Privacidade.</p>

      <h2>4. Uso do Site</h2>
      <p>Você concorda em utilizar o site apenas para fins legais e de acordo com estes termos. É proibido:</p>
      <ul>
        <li>Utilizar robôs, crawlers ou qualquer ferramenta automatizada para extrair dados do site sem autorização prévia.</li>
        <li>Interferir no funcionamento do site ou sobrecarregar nossos servidores.</li>
        <li>Utilizar o site para qualquer atividade fraudulenta ou ilícita.</li>
      </ul>

      <h2>5. Links para Terceiros</h2>
      <p>Nosso site contém links para sites de terceiros (empresas anunciantes, plataformas de recrutamento, redes sociais). Não temos controle sobre o conteúdo ou práticas de privacidade desses sites e não nos responsabilizamos por eles.</p>

      <h2>6. Isenção de Responsabilidade</h2>
      <p>O Mondywork não garante que:</p>
      <ul>
        <li>As vagas listadas ainda estejam disponíveis no momento da sua visita.</li>
        <li>As informações das vagas estejam completamente precisas ou atualizadas.</li>
        <li>O site esteja disponível de forma ininterrupta ou livre de erros.</li>
      </ul>
      <p>O uso do site é por sua conta e risco. Em nenhuma circunstância o Mondywork será responsável por danos diretos, indiretos, incidentais ou consequenciais decorrentes do uso ou da impossibilidade de uso do site.</p>

      <h2>7. Propriedade Intelectual</h2>
      <p>Todo o conteúdo original do Mondywork (design, código, texto, logotipo) é de propriedade exclusiva do Mondywork e está protegido por leis de direitos autorais. O conteúdo das vagas é propriedade de suas respectivas empresas anunciantes.</p>

      <h2>8. Alterações nos Termos</h2>
      <p>Estes termos podem ser alterados a qualquer momento. As alterações entrarão em vigor imediatamente após a publicação no site. Recomendamos que revise esta página periodicamente.</p>

      <h2>9. Legislação Aplicável</h2>
      <p>Estes termos são regidos pelas leis da República Federativa do Brasil. Qualquer disputa será resolvida no foro da comarca de São Paulo, SP.</p>

      <h2>10. Contato</h2>
      <p>Em caso de dúvidas sobre estes Termos de Uso, entre em contato pelo e-mail: <a href="mailto:hello@mondywork.com">hello@mondywork.com</a></p>
    </div>
  </section>
</main>

<?php renderBlogSection($blogPosts); ?>
<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="contato.php">Contato</a>
      <a class="footer-link" href="sobre.php">Sobre</a>
      <a class="footer-link" href="guia-de-carreira.php">Guia de Tecnologia</a>
      <a class="footer-link" href="guia-de-carreira-design.php">Guia de Design</a>
      <a class="footer-link" href="guia-de-carreira-marketing.php">Guia de Marketing</a>
      <a class="footer-link" href="guia-de-carreira-comunicacao.php">Guia de Comunicacao</a>
      <a class="footer-link" href="guia-de-carreira-administracao.php">Guia de Administracao</a>
      <a class="footer-link" href="guia-de-carreira-dados.php">Guia de Dados</a>
      <a class="footer-link" href="guia-de-carreira-produto.php">Guia de Produto</a>
      <a class="footer-link" href="guia-de-carreira-financas.php">Guia de Finanças</a>
      <a class="footer-link" href="privacidade.php">Privacidade</a>
      <a class="footer-link" href="termos-de-uso.php">Termos</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. Todos os direitos reservados.</p>
  </div>
</footer>

<div id="cookie-banner" class="cookie-banner">
  <p class="cookie-text">Utilizamos cookies para melhorar sua experiência e analisar o tráfego do site. Ao continuar navegando, você concorda com nossa <a href="privacidade.php">Política de Privacidade</a>.</p>
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

/* ==== MOBILE MENU ==== */
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
</script>
</body>
</html>
