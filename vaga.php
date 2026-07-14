<?php
$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if (!$id) {
    header('Location: /');
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    require_once __DIR__ . '/lib/Database.php';
    setupSchema($pdo);

    $stmt = $pdo->prepare("SELECT * FROM vagas WHERE vaga_id_externo = :id AND status = 'ativa' LIMIT 1");
    $stmt->execute([':id' => $id]);
    $vaga = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vaga) {
        $vaga['titulo'] = capitalizeTitle($vaga['titulo']);
        $stmtCat = $pdo->prepare("SELECT c.slug, c.nome_pt, c.nome_en FROM categorias c JOIN vaga_categorias vc ON c.id = vc.categoria_id WHERE vc.vaga_id = :id");
        $stmtCat->execute([':id' => $vaga['id']]);
        $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $vaga = null;
    $categorias = [];
}

$dicaPost = null;
$relatedPosts = [];
try {
    if ($vaga) {
        $stmtDica = $pdo->prepare("SELECT slug, title, excerpt, content, author, published_at FROM blog_posts WHERE status = 'publicado' ORDER BY RAND() LIMIT 1");
        $stmtDica->execute();
        $dicaPost = $stmtDica->fetch(PDO::FETCH_ASSOC);

        $stmtRelated = $pdo->prepare("SELECT slug, title, excerpt, image, categoria, author, published_at FROM blog_posts WHERE status = 'publicado' ORDER BY published_at DESC LIMIT 3");
        $stmtRelated->execute();
        $relatedPosts = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $dicaPost = null;
    $relatedPosts = [];
}

if (!$vaga) {
    http_response_code(404);
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function capitalizeTitle($str) {
    return mb_convert_case($str, MB_CASE_TITLE, 'UTF-8');
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

function blogExcerpt($text, $max = 350) {
    if (mb_strlen($text) <= $max) return $text;
    return mb_substr($text, 0, $max) . '...';
}

$isExterior = ($vaga ? $vaga['origem'] : 'nacional') === 'exterior';

if ($vaga) {
    $pageTitle = $vaga['titulo'] . ' na ' . $vaga['empresa'] . ' | Mondywork';
    $pageDesc = $vaga['resumo'] ?: 'Vaga de ' . $vaga['titulo'] . ' na ' . $vaga['empresa'] . '. ' . ($vaga['localizacao'] ?: 'Remoto') . '.';
    $ogUrl = 'https://mondywork.com/vaga/' . urlencode($vaga['vaga_id_externo']);
    $canonical = $ogUrl;

    $modeloLabel = $vaga['modelo_trabalho'] ? formatModelo($vaga['modelo_trabalho']) : null;
    $localLabel = $vaga['localizacao'] ?: ($isExterior ? 'Remote' : 'Remoto');

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $vaga['titulo'],
        'description' => strip_tags($vaga['descricao'] ?: $vaga['resumo'] ?: ''),
        'datePosted' => $vaga['publicado_em'] ? date('c', strtotime($vaga['publicado_em'])) : date('c'),
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => $vaga['empresa'],
        ],
        'jobLocation' => [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $vaga['localizacao'] ?: ($isExterior ? 'Remote' : 'Remoto'),
                'addressCountry' => $isExterior ? 'US' : 'BR',
            ],
        ],
        'url' => $ogUrl,
        'directApply' => true,
        'applicantLocationRequirements' => [
            '@type' => 'Country',
            'name' => $isExterior ? 'US' : 'BR',
        ],
    ];
    $jsonLd['employmentType'] = 'FULL_TIME';
    if ($vaga['modelo_trabalho']) {
        $modeloLower = mb_strtolower($vaga['modelo_trabalho']);
        if (in_array($modeloLower, ['remote', 'remoto'])) {
            $jsonLd['jobLocationType'] = 'TELECOMMUTE';
        }
    }
    if (!empty($categorias)) {
        $jsonLd['occupationalCategory'] = $categorias[0]['nome_pt'] ?? $categorias[0]['nome_en'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $isExterior ? 'en' : 'pt-BR' ?>">
<head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<?php if ($vaga): ?>
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="<?= esc($pageDesc) ?>">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= esc($ogUrl) ?>">
<meta property="og:title" content="<?= esc($pageTitle) ?>">
<meta property="og:description" content="<?= esc($pageDesc) ?>">
<meta property="og:image" content="https://mondywork.com/img/og-image.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?= esc($ogUrl) ?>">
<meta property="twitter:title" content="<?= esc($pageTitle) ?>">
<meta property="twitter:description" content="<?= esc($pageDesc) ?>">
<meta property="twitter:image" content="https://mondywork.com/img/og-image.jpg">
<?php if ($vaga): ?>
<meta property="article:published_time" content="<?= esc(date('c', strtotime($vaga['publicado_em']))) ?>">
<?php if (!empty($categorias)): ?>
<meta property="article:section" content="<?= esc($categorias[0]['nome_pt'] ?? $categorias[0]['nome_en']) ?>">
<?php endif; ?>
<?php endif; ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php if ($vaga): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Mondywork", "item": "https://mondywork.com/" },
    { "@type": "ListItem", "position": 2, "name": "<?= esc($vaga['titulo']) ?>", "item": "<?= esc($ogUrl) ?>" }
  ]
}
</script>
<?php endif; ?>
<?php else: ?>
<title>Vaga não encontrada | Mondywork</title>
<meta name="robots" content="noindex">
<?php endif; ?>
<?php if ($vaga): ?>
<link rel="alternate" hreflang="pt-BR" href="https://mondywork.com/vaga/<?= esc($vaga['vaga_id_externo']) ?>">
<link rel="alternate" hreflang="en" href="https://mondywork.com/usa/vaga/<?= esc($vaga['vaga_id_externo']) ?>">
<?php endif; ?>
<link rel="stylesheet" href="/css/style.css?v=1.7.3">
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
      <a class="nav-link" href="/sobre.php"><?= $isExterior ? 'About' : 'Sobre' ?></a>
      <a class="nav-link" href="/contato.php"><?= $isExterior ? 'Contact' : 'Contato' ?></a>
<?php if (!$isExterior): ?>
<?php endif; ?>
    </div>
  </div>
</nav>

<main class="main-content" style="padding-top: 80px;">
  <div class="section" style="max-width: 800px; margin: 0 auto; padding: 0 16px;">

<?php if ($vaga): ?>

    <a href="<?= $isExterior ? '/usa/' : '/vagas/' ?>" class="job-card-btn" style="display: inline-flex; margin-bottom: 24px; text-decoration: none;">&larr; <?= $isExterior ? 'Back to jobs' : 'Voltar para vagas' ?></a>

    <article class="vaga-page">
   
      <header class="vaga-page-header">
        <h1 class="vaga-page-title"><?= esc($vaga['titulo']) ?></h1>
        <p class="vaga-page-company"><?= esc($vaga['empresa']) ?></p>
        <div class="job-card-info" style="margin-top: 12px;">
<?php if ($modeloLabel): ?>
          <span class="<?= badgeClass($vaga['modelo_trabalho']) ?>"><?= esc($modeloLabel) ?></span>
<?php endif; ?>
          <span class="job-card-info-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0116 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= esc($localLabel) ?>
          </span>
<?php if ($vaga['publicado_em']): ?>
          <span class="job-card-info-text job-card-date">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:middle;margin-right:4px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <time datetime="<?= date('Y-m-d', strtotime($vaga['publicado_em'])) ?>"><?= date($isExterior ? 'M d, Y' : 'd/m/Y', strtotime($vaga['publicado_em'])) ?></time>
          </span>
<?php endif; ?>
        </div>
<?php if (!empty($categorias)): ?>
        <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 6px;">
<?php foreach ($categorias as $cat): ?>
          <span class="badge" style="background: rgba(75, 65, 225, 0.1); color: #4b41e1; border: 1px solid rgba(75, 65, 225, 0.2);"><?= esc($isExterior ? $cat['nome_en'] : $cat['nome_pt']) ?></span>
<?php endforeach; ?>
        </div>
<?php endif; ?>
      </header>

<?php
$score = 0;
$scoreDetails = [];
$modelo = $vaga['modelo_trabalho'] ? mb_strtolower($vaga['modelo_trabalho']) : '';

if (in_array($modelo, ['remote', 'remoto'])) {
    $score += 40;
    $scoreDetails[] = $isExterior ? '100% remote' : '100% remoto';
} elseif (in_array($modelo, ['hybrid', 'hibrido'])) {
    $score += 25;
    $scoreDetails[] = $isExterior ? 'hybrid model' : 'modelo híbrido';
} else {
    $score += 10;
    $scoreDetails[] = $isExterior ? 'on-site' : 'presencial';
}

$catNames = [];
foreach ($categorias as $cat) {
    $catName = $isExterior ? $cat['nome_en'] : $cat['nome_pt'];
    $slug = $cat['slug'];
    if (!in_array($slug, ['sem-categoria'])) {
        $catNames[] = $catName;
        $score += 20;
    }
}
$score = min($score, 100);

if ($score >= 80) {
    $compatLevel = $isExterior ? 'excellent' : 'excelente';
    $compatLabel = $isExterior ? 'Excellent match' : 'Excelente compatibilidade';
} elseif ($score >= 50) {
    $compatLevel = 'good';
    $compatLabel = $isExterior ? 'Good match' : 'Boa compatibilidade';
} else {
    $compatLevel = 'basic';
    $compatLabel = $isExterior ? 'Basic match' : 'Compatibilidade básica';
}

$compatText = '';
if ($isExterior) {
    $compatText = 'This position';
    if (!empty($catNames)) {
        $compatText .= ' in <strong>' . esc(implode(' / ', $catNames)) . '</strong>';
    }
    $compatText .= ' is a ' . $compatLabel . ' for professionals';
    if ($score >= 80) {
        $compatText .= ' seeking ' . esc(implode(' and ', $scoreDetails)) . ' opportunities.';
    } elseif ($score >= 50) {
        $compatText .= '. ';
        $compatText .= 'Model: ' . esc(implode(', ', $scoreDetails)) . '.';
    } else {
        $compatText .= ' looking for ' . esc(implode(', ', $scoreDetails)) . ' roles.';
    }
} else {
    $compatText = 'Esta vaga';
    if (!empty($catNames)) {
        $compatText .= ' na área de <strong>' . esc(implode(' / ', $catNames)) . '</strong>';
    }
    $compatText .= ' possui ' . mb_strtolower($compatLabel) . ' para profissionais';
    if ($score >= 80) {
        $compatText .= ' que buscam oportunidades ' . esc(implode(' e ', $scoreDetails)) . '.';
    } elseif ($score >= 50) {
        $compatText .= '. ';
        $compatText .= 'Modelo de trabalho: ' . esc(implode(', ', $scoreDetails)) . '.';
    } else {
        $compatText .= ' em busca de vagas ' . esc(implode(', ', $scoreDetails)) . '.';
    }
}
?>

      <div class="vaga-compat">
        <div class="vaga-compat-header">
          <div class="vaga-compat-score <?= $compatLevel ?>">
            <span class="vaga-compat-number"><?= $score ?>%</span>
            <span class="vaga-compat-label"><?= $compatLabel ?></span>
          </div>
        </div>
        <div class="vaga-compat-body">
          <p><?= $compatText ?></p>
        </div>
      </div>

      <div class="vaga-page-body">
        <?= $vaga['descricao'] ?: '<p>' . ($isExterior ? 'Description not available.' : 'Descrição não disponível.') . '</p>' ?>
      </div>

      <div class="vaga-page-footer">
        <a href="<?= esc($vaga['url_vaga']) ?>" target="_blank" rel="noopener noreferrer" class="modal-btn"><?= $isExterior ? 'Apply Now' : 'Aplicar na Vaga' ?></a>
      </div>
    </article>

<?php if ($dicaPost): ?>
    <div class="dica-expert">
      <div class="dica-expert-header">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:24px;height:24px;color:#4b41e1;flex-shrink:0"><path d="M12 2a7 7 0 017 7c0 2.38-1.19 4.47-3 5.74V17a2 2 0 01-2 2h-4a2 2 0 01-2-2v-2.26C6.19 13.47 5 11.38 5 9a7 7 0 017-7z"/><line x1="9" y1="21" x2="15" y2="21"/></svg>
        <div>
          <h3 class="dica-expert-title">Dica do Especialista</h3>
          <p class="dica-expert-subtitle"><?= esc($dicaPost['title']) ?></p>
        </div>
      </div>
      <div class="dica-expert-body">
        <?= $dicaPost['content'] ?>
      </div>
      <div class="dica-expert-footer">
        <a href="/blog/<?= esc($dicaPost['slug']) ?>" class="dica-expert-link">Ler artigo completo &rarr;</a>
      </div>
    </div>
<?php endif; ?>

<?php if (!empty($relatedPosts)): ?>
    <div class="related-posts">
      <h3 class="related-posts-title">Artigos Relacionados</h3>
      <div class="related-posts-grid">
        <?php foreach ($relatedPosts as $p):
          $rImg = $p['image'] ?: '';
          $rDate = $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : '';
        ?>
        <a href="/blog/<?= esc($p['slug']) ?>" class="related-post-card">
          <?php if ($rImg): ?>
            <div class="related-post-image" style="background-image:url('<?= esc($rImg) ?>')"></div>
          <?php else: ?>
            <div class="related-post-image related-post-image--empty"><?= esc(mb_substr($p['title'], 0, 1)) ?></div>
          <?php endif; ?>
          <div class="related-post-body">
            <?php if (!empty($p['categoria'])): ?>
              <span class="blog-card-cat"><?= esc($p['categoria']) ?></span>
            <?php endif; ?>
            <h4 class="related-post-title"><?= esc($p['title']) ?></h4>
            <p class="related-post-excerpt"><?= esc(blogExcerpt(strip_tags($p['excerpt'] ?: ''))) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
<?php endif; ?>

<?php else: ?>

    <div style="text-align:center;padding:80px 0">
      <h1 style="font-size:2rem;margin-bottom:16px"><?= $isExterior ? 'Job not found' : 'Vaga não encontrada' ?></h1>
      <p style="color:#666;margin-bottom:24px"><?= $isExterior ? 'This job is no longer available or the link is invalid.' : 'Esta vaga não está mais disponível ou o link é inválido.' ?></p>
      <a href="<?= $isExterior ? '/usa/' : '/vagas/' ?>" class="modal-btn" style="display:inline-block;text-decoration:none">&larr; <?= $isExterior ? 'Back to jobs' : 'Voltar para vagas' ?></a>
    </div>

<?php endif; ?>

  </div>
</main>

<footer class="footer">
  <div class="footer-inner">
    <span class="footer-logo">Mondywork</span>
    <div class="footer-links">
      <a class="footer-link" href="/contato.php"><?= $isExterior ? 'Contact' : 'Contato' ?></a>
      <a class="footer-link" href="/sobre.php"><?= $isExterior ? 'About' : 'Sobre' ?></a>
<?php if ($isExterior): ?>
      <a class="footer-link" href="/usa/privacy.php">Privacy</a>
      <a class="footer-link" href="/usa/terms.php">Terms</a>
<?php else: ?>
      <a class="footer-link" href="/privacidade.php">Privacidade</a>
      <a class="footer-link" href="/termos-de-uso.php">Termos</a>
<?php endif; ?>
    </div>
    <p class="footer-text">&copy; 2026 Mondywork. <?= $isExterior ? 'All rights reserved.' : 'Todos os direitos reservados.' ?></p>
  </div>
</footer>


</body>
</html>
