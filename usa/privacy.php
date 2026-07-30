<?php
$configFile = file_exists(__DIR__ . '/../config.local.php') ? __DIR__ . '/../config.local.php' : __DIR__ . '/../config.php';
$config = require $configFile;
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/BlogHelper.php';
try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    setupSchema($pdo);
    $blogPosts = getBlogPosts($pdo, 9, 'en');
} catch (Exception $e) { $blogPosts = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Privacy Policy | Mondywork</title>
<meta name="description" content="Mondywork Privacy Policy. Learn how we collect, use, and protect your information.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com/usa/privacy.php">
<meta property="og:title" content="Privacy Policy | Mondywork">
<meta property="og:description" content="Mondywork Privacy Policy. Learn how we collect, use, and protect your information.">
<meta property="og:image" content="https://mondywork.com/img/og-image-usa.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com/usa/privacy.php">
<meta property="twitter:title" content="Privacy Policy | Mondywork">
<meta property="twitter:description" content="Mondywork Privacy Policy. Learn how we collect, use, and protect your information.">
<meta property="twitter:image" content="https://mondywork.com/img/og-image-usa.jpg">
<link rel="stylesheet" href="/css/style.css?v=1.8.0">
<link rel="icon" href="../img/favicon/favicon.ico" sizes="any">
<link rel="icon" href="../img/favicon/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="../img/favicon/apple-touch-icon.png">
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
      <a class="nav-link nav-btn" href="/vagas/">Vagas</a>
      <a class="nav-link" href="about.php">About</a>
      <a class="nav-link" href="contact.php">Contact</a>
      <a class="nav-link" href="/"><svg width="18" height="12" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:4px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
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
  <a class="nav-link nav-btn" href="/vagas/">Vagas</a>
      <a class="nav-link" href="about.php">About</a>
  <a class="nav-link" href="contact.php">Contact</a>
  <a class="nav-link" href="/"><svg width="20" height="14" viewBox="0 0 18 12" style="vertical-align:middle;margin-right:6px"><rect width="18" height="12" rx="1.5" fill="#009739"/><polygon points="9,2 15,6 9,10 3,6" fill="#FEDD00"/><circle cx="9" cy="6" r="2.5" fill="#002776"/></svg>Jobs in Brazil</a>
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
      <h1 class="legal-title">Privacy Policy</h1>
      <p class="legal-update">Last updated: May 2026</p>

      <p>Mondywork values your privacy. This Privacy Policy describes how we collect, use, store, and protect the personal information you provide when using our website.</p>

      <h2>1. Information We Collect</h2>
      <p>We may collect the following information when you use our website:</p>
      <ul>
        <li><strong>Registration information:</strong> name and email address, voluntarily provided through our newsletter signup form.</li>
        <li><strong>Browsing data:</strong> IP address, browser type, pages visited, time spent, and other information automatically collected through cookies and analytics tools (Google Analytics).</li>
      </ul>

      <h2>2. How We Use Your Data</h2>
      <p>The information collected is used to:</p>
      <ul>
        <li>Send newsletters with job listings and opportunities (upon voluntary signup).</li>
        <li>Improve user experience and optimize the website.</li>
        <li>Analyze usage trends and audience.</li>
        <li>Comply with legal and regulatory obligations.</li>
      </ul>

      <h2>3. Data Sharing</h2>
      <p>We do not sell, trade, or share your personal information with third parties, except:</p>
      <ul>
        <li>When required by law or court order.</li>
        <li>With service providers who assist us in operating our website (such as Google Analytics and hosting services), provided they agree to keep the data confidential.</li>
      </ul>

      <h2>4. Cookies</h2>
      <p>We use cookies and similar technologies to improve website functionality and understand how you interact with our content. You can configure your browser to refuse cookies, but this may affect some website features.</p>
      <p>We use Google Analytics cookies to analyze traffic and user behavior. Google may use this data in accordance with its <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a>.</p>

      <h2>5. Storage and Security</h2>
      <p>We implement appropriate technical and organizational measures to protect your data against unauthorized access, alteration, disclosure, or destruction. Data is stored on secure servers with restricted access.</p>

      <h2>6. Your Rights</h2>
      <p>You have the right to:</p>
      <ul>
        <li>Request access, correction, or deletion of your personal data.</li>
        <li>Unsubscribe from our newsletter at any time (simply reply to the email or use the unsubscribe link).</li>
        <li>Contact us with any questions about this policy.</li>
      </ul>

      <h2>7. Changes to This Policy</h2>
      <p>This policy may be updated periodically. We recommend reviewing this page regularly to stay informed about how we protect your information.</p>

      <h2>8. Contact</h2>
      <p>If you have any questions about this Privacy Policy, please contact us at: <a href="mailto:hello@mondywork.com">hello@mondywork.com</a></p>
    </div>
  </section>
</main>

<?php renderBlogSection($blogPosts, 'en'); ?>
<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="contact.php">Contact</a>
      <a class="footer-link" href="about.php">About</a>
      <a class="footer-link" href="privacy.php">Privacy</a>
      <a class="footer-link" href="terms.php">Terms</a>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. All rights reserved.</p>
  </div>
</footer>

<div id="modal-overlay" class="modal-overlay hidden">
  <div class="modal-content" role="dialog" aria-modal="true">
    <div class="modal-header">
      <div>
        <h2 id="modal-title" class="modal-title"></h2>
        <p id="modal-subtitle" class="modal-subtitle"></p>
      </div>
      <button id="modal-close" class="modal-close" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div id="modal-body" class="modal-body"></div>
    <div class="modal-footer" id="modal-footer">
      <a id="modal-apply" href="#" target="_blank" class="modal-btn">Close</a>
    </div>
  </div>
</div>

<div id="cookie-banner" class="cookie-banner">
  <p class="cookie-text">We use cookies to improve your experience and analyze site traffic. By continuing to browse, you agree to our <a href="privacy.php">Privacy Policy</a>.</p>
  <button id="cookie-accept" class="cookie-btn">Accept</button>
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

<script src="/js/app-exterior.js?v=2.2.0"></script>
</body>
</html>
