<?php
$configFile = file_exists(__DIR__ . '/../config.local.php') ? __DIR__ . '/../config.local.php' : __DIR__ . '/../config.php';
$config = require $configFile;
require_once __DIR__ . '/../lib/Database.php';
require_once __DIR__ . '/../lib/BlogHelper.php';
try {
    $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    setupSchema($pdo);
    $blogPosts = getBlogPosts($pdo);
} catch (Exception $e) { $blogPosts = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<title>Contact | Mondywork</title>
<meta name="description" content="Contact Mondywork. Get in touch with questions, suggestions, or partnership inquiries.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://mondywork.com/usa/contact.php">
<meta property="og:title" content="Contact | Mondywork">
<meta property="og:description" content="Contact Mondywork. Get in touch with questions, suggestions, or partnership inquiries.">
<meta property="og:image" content="https://mondywork.com/img/og-image-usa.jpg">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://mondywork.com/usa/contact.php">
<meta property="twitter:title" content="Contact | Mondywork">
<meta property="twitter:description" content="Contact Mondywork. Get in touch with questions, suggestions, or partnership inquiries.">
<meta property="twitter:image" content="https://mondywork.com/img/og-image-usa.jpg">
<link rel="stylesheet" href="/css/style.css?v=1.7.1">
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
      <a class="nav-link" href="about.php">About</a>
      <a class="nav-link active" href="contact.php">Contact</a>
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
  <a class="nav-link" href="about.php">About</a>
  <a class="nav-link active" href="contact.php">Contact</a>
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
      <h1 class="legal-title">Contact</h1>

      <p>Have questions, suggestions, or interested in partnerships? Get in touch with us! We'll respond as soon as possible.</p>

      <p style="font-size:1.5rem;font-weight:600;color:#4b41e1;text-align:center;padding:32px 0">
        <a href="mailto:hello@mondywork.com" style="color:#4b41e1;text-decoration:none">hello@mondywork.com</a>
      </p>

      <h2>Social Media</h2>
      <p>Follow Mondywork on social media to stay up to date:</p>
      <ul>
        <li><strong>X (Twitter):</strong> <a href="https://x.com/mondywork" target="_blank">@mondywork</a></li>
        <li><strong>LinkedIn:</strong> <a href="https://www.linkedin.com/company/mondywork/" target="_blank">/company/mondywork</a></li>
      </ul>

      <h2>Response Time</h2>
      <p>We strive to respond to all messages within 48 business hours.</p>
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
