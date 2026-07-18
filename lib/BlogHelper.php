<?php
function getBlogPosts($pdo, $limit = 9, $lang = 'pt') {
    try {
        $stmt = $pdo->prepare("SELECT slug, title, excerpt, image, categoria, author, published_at FROM blog_posts WHERE status = 'publicado' AND lang = :lang ORDER BY published_at DESC LIMIT :lim");
        $stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

function blogExcerpt($text, $max = 350) {
    if (mb_strlen($text) <= $max) return $text;
    return mb_substr($text, 0, $max) . '...';
}

function renderBlogSection($blogPosts, $lang = 'pt') {
    if (empty($blogPosts)) return;
    $isEn = $lang === 'en';
    $sectionTitle = $isEn ? 'Latest Articles' : 'Artigos Recentes';
    $sectionDesc = $isEn
        ? 'Read our articles about career, job market and professional development in Technology, Design, Marketing and Product.'
        : 'Confira nossos artigos sobre carreira, mercado de trabalho e desenvolvimento profissional em Tecnologia, Design, Marketing e Produto.';
    $btnText = $isEn ? 'View all articles' : 'Ver todos os artigos';
    $esc = function($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
?>
  <section class="section">
    <div class="section-header">
      <h2 class="section-title"><?= $sectionTitle ?></h2>
    </div>
    <p class="section-description"><?= $sectionDesc ?></p>
    <div class="blog-grid">
      <?php foreach ($blogPosts as $p):
        $img = $p['image'] ?: '';
        $date = $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : '';
      ?>
      <article class="blog-card">
        <a href="/blog/<?= $esc($p['slug']) ?>" class="blog-card-link">
          <?php if ($img): ?>
            <div class="blog-card-image" style="background-image:url('<?= $esc($img) ?>')"></div>
          <?php else: ?>
            <div class="blog-card-image blog-card-image--empty"><?= $esc(mb_substr($p['title'], 0, 1)) ?></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <?php if (!empty($p['categoria'])): ?>
              <span class="blog-card-cat"><?= $esc($p['categoria']) ?></span>
            <?php endif; ?>
            <h3 class="blog-card-title"><?= $esc($p['title']) ?></h3>
            <p class="blog-card-excerpt"><?= $esc(blogExcerpt(strip_tags($p['excerpt'] ?: ''))) ?></p>
            <div class="blog-card-meta">
              <span><?= $esc($p['author']) ?></span>
              <span><?= $date ?></span>
            </div>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:32px">
      <a href="/" class="btn-clear" style="display:inline-flex;align-items:center"><?= $btnText ?> &rarr;</a>
    </div>
  </section>
<?php
}
