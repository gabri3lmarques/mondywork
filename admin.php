<?php
session_start();

$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;
$adminPassword = $config['admin_password'] ?? '';

if ($adminPassword === '') {
    http_response_code(500);
    exit('Admin password not configured.');
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (hash_equals($adminPassword, $_POST['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Senha incorreta';
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_ids']) && isset($_POST['batch_action'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $ids = array_map('intval', explode(',', $_POST['batch_ids']));
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $targetStatus = $_POST['batch_action'] === 'ativar' ? 'ativa' : 'inativa';
            $stmt = $pdo->prepare("UPDATE vagas SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$targetStatus], $ids));
        }
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . (isset($_GET['origem']) ? '&origem=' . urlencode($_GET['origem']) : ''));
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmt = $pdo->prepare("UPDATE vagas SET status = IF(status = 'ativa', 'inativa', 'ativa') WHERE id = :id");
        $stmt->execute([':id' => (int)$_POST['toggle_id']]);
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . (isset($_GET['origem']) ? '&origem=' . urlencode($_GET['origem']) : ''));
    exit;
}

$origemFilter = isset($_GET['origem']) && in_array($_GET['origem'], ['nacional', 'exterior']) ? $_GET['origem'] : '';

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Mondywork</title>
<link rel="stylesheet" href="/css/style.css?v=1.9">
<style>
.admin-nav { background: #0b1c30; height: 64px; }
.admin-nav .nav-inner { height: 64px; }
.admin-nav .nav-logo { color: #fff; font-size: 1.25rem; }
.admin-link { font-size: 14px; font-weight: 500; color: #c6c6cd; transition: color 0.2s; }
.admin-link:hover { color: #fff; }
.admin-link.active { color: #fff; }
.login-wrap { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 16px; }
.login-card { background: #fff; border-radius: 0.75rem; padding: 48px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #c6c6cd; width: 100%; max-width: 400px; }
.login-card h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
.login-card p { color: #45464d; font-size: 14px; margin-bottom: 24px; }
.login-card input { width: 100%; background: #fff; border: 1px solid #c6c6cd; border-radius: 0.5rem; padding: 12px 16px; font-size: 16px; color: #0b1c30; outline: none; transition: border-color 0.2s; }
.login-card input:focus { border-color: #4b41e1; box-shadow: 0 0 0 1px #4b41e1; }
.login-card button { width: 100%; background: #4b41e1; color: #fff; font-size: 14px; font-weight: 700; padding: 12px 24px; border: none; border-radius: 0.5rem; cursor: pointer; margin-top: 16px; transition: background 0.3s; }
.login-card button:hover { background: #645efb; }
.login-error { color: #ba1a1a; font-size: 14px; font-weight: 500; margin-bottom: 16px; }
.admin-main { max-width: 1280px; margin: 96px auto 48px; padding: 0 16px; }
@media (min-width: 768px) { .admin-main { padding: 0 48px; } }
.admin-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
.admin-header h1 { font-size: 1.5rem; font-weight: 700; }
.admin-header span { color: #45464d; font-size: 14px; font-weight: 500; }
.admin-stats { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
.stat { background: #fff; border: 1px solid #c6c6cd; border-radius: 0.5rem; padding: 12px 20px; font-size: 14px; font-weight: 500; }
.stat strong { color: #4b41e1; }
.admin-card { background: #fff; border-radius: 0.75rem; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #c6c6cd; transition: box-shadow 0.3s; }
.admin-card:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
.admin-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.admin-card-title { font-size: 18px; line-height: 24px; font-weight: 600; color: #0b1c30; }
.admin-card-company { font-size: 13px; line-height: 18px; font-weight: 500; color: #45464d; margin-top: 2px; }
.admin-card-meta { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 12px; }
.admin-card-meta span { font-size: 12px; line-height: 16px; font-weight: 600; color: #45464d; display: inline-flex; align-items: center; gap: 4px; }
.badge-origem { background: #e5eeff; color: #0b1c30; border: 1px solid #c6c6cd; border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-origem.exterior { background: #fff3e5; color: #b55a00; border-color: #ffe0b3; }
.badge-status { border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-status.ativa { background: #e6f7e6; color: #1a7d1a; border: 1px solid #b3e6b3; }
.badge-status.inativa { background: #fde8e8; color: #ba1a1a; border: 1px solid #f5baba; }
.admin-card-actions { margin-top: 12px; display: flex; gap: 8px; }
.btn-toggle { font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 0.5rem; border: 1px solid; cursor: pointer; transition: background 0.3s; }
.btn-toggle.inativar { color: #ba1a1a; border-color: #f5baba; background: transparent; }
.btn-toggle.inativar:hover { background: #fde8e8; }
.btn-toggle.ativar { color: #1a7d1a; border-color: #b3e6b3; background: transparent; }
.btn-toggle.ativar:hover { background: #e6f7e6; }
.admin-pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 32px; flex-wrap: wrap; }
.admin-pagination a, .admin-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px; border: 1px solid #c6c6cd; border-radius: 0.5rem; font-size: 14px; font-weight: 500; color: #0b1c30; background: #fff; transition: all 0.2s; }
.admin-pagination a:hover { border-color: #4b41e1; color: #4b41e1; }
.admin-pagination .current { background: #4b41e1; color: #fff; border-color: #4b41e1; }
.admin-empty { text-align: center; padding: 64px 16px; color: #45464d; }
.admin-tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid #c6c6cd; padding-bottom: 0; }
.admin-tab { padding: 10px 20px; font-size: 14px; font-weight: 600; color: #45464d; border: 1px solid transparent; border-bottom: none; border-radius: 0.5rem 0.5rem 0 0; transition: all 0.2s; margin-bottom: -1px; }
.admin-tab:hover { color: #4b41e1; }
.admin-tab.active { color: #4b41e1; background: #fff; border-color: #c6c6cd; }
.badge-velha { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.batch-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; padding: 12px 16px; background: #fff; border: 1px solid #c6c6cd; border-radius: 0.5rem; }
.batch-select-all { font-size: 14px; font-weight: 500; color: #0b1c30; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.batch-select-all input { width: 16px; height: 16px; cursor: pointer; }
.batch-count { font-size: 13px; color: #45464d; margin-right: auto; }
.admin-check-wrap { display: flex; align-items: center; padding: 4px 8px 0 0; }
.admin-check-wrap input { width: 18px; height: 18px; cursor: pointer; }
</style>
</head>
<body>

<nav class="nav admin-nav">
  <div class="nav-inner">
    <a class="nav-logo" href="admin.php">Mondywork Admin</a>
    <div class="nav-links">
      <a class="admin-link" href="/" target="_blank">Ver Site</a>
      <a class="admin-link" href="admin.php?logout">Sair</a>
    </div>
  </div>
</nav>

<?php if (!$isLoggedIn): ?>

<div class="login-wrap">
  <div class="login-card">
    <h1>Painel Admin</h1>
    <p>Insira a senha para acessar</p>
    <?php if (!empty($loginError)): ?>
      <p class="login-error"><?php echo $loginError ?></p>
    <?php endif; ?>
    <form method="post">
      <input type="password" name="password" placeholder="Senha" autofocus>
      <button type="submit">Entrar</button>
    </form>
  </div>
</div>

<?php else:

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $origemCondicao = $origemFilter !== '' ? " WHERE origem = " . $pdo->quote($origemFilter) : '';

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 30;
    $offset = ($page - 1) * $limit;

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM vagas" . $origemCondicao);
    $totalVagas = (int)$totalStmt->fetchColumn();

    $ativasStmt = $pdo->query("SELECT COUNT(*) FROM vagas WHERE status = 'ativa'" . ($origemFilter !== '' ? " AND origem = " . $pdo->quote($origemFilter) : ''));
    $totalAtivas = (int)$ativasStmt->fetchColumn();

    $inativasStmt = $pdo->query("SELECT COUNT(*) FROM vagas WHERE status = 'inativa'" . ($origemFilter !== '' ? " AND origem = " . $pdo->quote($origemFilter) : ''));
    $totalInativas = (int)$inativasStmt->fetchColumn();

    $totalPages = $totalVagas > 0 ? (int)ceil($totalVagas / $limit) : 1;

    $stmt = $pdo->prepare("SELECT id, vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, resumo, status, origem, publicado_em, DATE_FORMAT(publicado_em, '%d/%m/%Y') as publicado_em_fmt FROM vagas" . $origemCondicao . " ORDER BY publicado_em DESC, data_coleta DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="admin-main"><p style="color:#ba1a1a;">Erro ao conectar ao banco de dados.</p></div>';
    exit;
}
?>

<main class="admin-main">
  <div class="admin-header">
    <div>
      <h1>Vagas</h1>
      <span><?php echo $totalVagas ?> vagas no total</span>
    </div>
  </div>

  <div class="admin-stats">
    <div class="stat">Total: <strong><?php echo $totalVagas ?></strong></div>
    <div class="stat">Ativas: <strong style="color:#1a7d1a"><?php echo $totalAtivas ?></strong></div>
    <div class="stat">Inativas: <strong style="color:#ba1a1a"><?php echo $totalInativas ?></strong></div>
  </div>

  <div class="admin-tabs">
    <a class="admin-tab <?php echo $origemFilter === '' ? 'active' : '' ?>" href="admin.php">Todas</a>
    <a class="admin-tab <?php echo $origemFilter === 'nacional' ? 'active' : '' ?>" href="admin.php?origem=nacional">Brasil</a>
    <a class="admin-tab <?php echo $origemFilter === 'exterior' ? 'active' : '' ?>" href="admin.php?origem=exterior">Exterior</a>
  </div>

  <div class="batch-bar" id="batch-bar">
    <label class="batch-select-all"><input type="checkbox" id="select-all"> Selecionar todas</label>
    <span class="batch-count" id="batch-count">0 selecionadas</span>
    <button type="button" class="btn-toggle inativar" id="btn-batch-inativar" onclick="batchToggle('inativar')">Inativar Selecionadas</button>
    <button type="button" class="btn-toggle ativar" id="btn-batch-ativar" onclick="batchToggle('ativar')">Ativar Selecionadas</button>
  </div>

  <?php if (empty($vagas)): ?>
    <div class="admin-empty">Nenhuma vaga encontrada.</div>
  <?php else:
    foreach ($vagas as $v): ?>
      <div class="admin-card" style="margin-bottom:12px">
        <div class="admin-card-header">
          <label class="admin-check-wrap">
            <input type="checkbox" class="batch-check" value="<?php echo (int)$v['id'] ?>" data-status="<?php echo $v['status'] ?>">
          </label>
          <div style="flex:1">
            <div class="admin-card-title"><?php echo htmlspecialchars($v['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-card-company"><?php echo htmlspecialchars($v['empresa'], ENT_QUOTES, 'UTF-8') ?><?php echo $v['localizacao'] ? ' • ' . htmlspecialchars($v['localizacao'], ENT_QUOTES, 'UTF-8') : '' ?></div>
          </div>
        </div>
        <div class="admin-card-meta">
          <span class="<?php echo $v['origem'] === 'exterior' ? 'badge-origem exterior' : 'badge-origem' ?>"><?php echo $v['origem'] === 'exterior' ? 'Exterior' : 'Brasil' ?></span>
          <?php if ($v['modelo_trabalho']): ?>
            <span class="badge badge-<?php echo htmlspecialchars(strtolower($v['modelo_trabalho']), ENT_QUOTES, 'UTF-8') === 'remote' ? 'remote' : (strtolower($v['modelo_trabalho']) === 'hybrid' ? 'hybrid' : 'onsite') ?>"><?php echo htmlspecialchars($v['modelo_trabalho'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <span class="badge-status <?php echo $v['status'] ?>"><?php echo $v['status'] === 'ativa' ? 'Ativa' : 'Inativa' ?></span>
          <?php if ($v['publicado_em']): ?>
            <?php $isOld = strtotime($v['publicado_em']) < strtotime('-90 days'); ?>
            <span><?php echo htmlspecialchars($v['publicado_em_fmt'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php if ($isOld): ?>
              <span class="badge-velha">90+ dias</span>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php if ($v['resumo']): ?>
          <p style="font-size:14px;line-height:20px;color:#45464d;margin-top:12px"><?php echo htmlspecialchars(mb_substr($v['resumo'], 0, 200), ENT_QUOTES, 'UTF-8') ?><?php echo mb_strlen($v['resumo']) > 200 ? '...' : '' ?></p>
        <?php endif; ?>
        <div class="admin-card-actions">
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle <?php echo $v['status'] === 'ativa' ? 'inativar' : 'ativar' ?>"><?php echo $v['status'] === 'ativa' ? 'Inativar' : 'Ativar' ?></button>
          </form>
          <?php if ($v['vaga_id_externo']): ?>
            <a href="/#<?php echo urlencode($v['vaga_id_externo']) ?>" target="_blank" style="font-size:13px;font-weight:500;color:#4b41e1;padding:7px 18px;border:1px solid #4b41e1;border-radius:0.5rem;text-decoration:none;display:inline-flex;align-items:center">Ver no site</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if ($totalPages > 1): ?>
      <?php $origemParam = $origemFilter !== '' ? '&origem=' . urlencode($origemFilter) : ''; ?>
      <div class="admin-pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1 . $origemParam ?>">&laquo;</a>
        <?php endif; ?>
        <?php
        $startPage = max(1, $page - 4);
        $endPage = min($totalPages, $page + 4);
        for ($i = $startPage; $i <= $endPage; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="current"><?php echo $i ?></span>
          <?php else: ?>
            <a href="?page=<?php echo $i . $origemParam ?>"><?php echo $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?php echo $page + 1 . $origemParam ?>">&raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <form id="batch-form" method="post" style="display:none">
    <input type="hidden" name="batch_ids" id="batch-ids">
    <input type="hidden" name="batch_action" id="batch-action">
  </form>
</main>

<script>
(function() {
  var selectAll = document.getElementById('select-all');
  var checks = document.querySelectorAll('.batch-check');
  var countEl = document.getElementById('batch-count');
  var batchBar = document.getElementById('batch-bar');

  function updateCount() {
    var checked = document.querySelectorAll('.batch-check:checked').length;
    countEl.textContent = checked + ' selecionada(s)';
    if (selectAll) selectAll.checked = checked === checks.length;
  }

  if (selectAll) {
    selectAll.addEventListener('change', function() {
      checks.forEach(function(cb) { cb.checked = selectAll.checked; });
      updateCount();
    });
  }

  checks.forEach(function(cb) {
    cb.addEventListener('change', updateCount);
  });

  window.batchToggle = function(action) {
    var ids = [];
    document.querySelectorAll('.batch-check:checked').forEach(function(cb) {
      ids.push(cb.value);
    });
    if (!ids.length) return;
    var msg = action === 'inativar' ? 'Inativar ' + ids.length + ' vaga(s)?' : 'Ativar ' + ids.length + ' vaga(s)?';
    if (!confirm(msg)) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = action;
    document.getElementById('batch-form').submit();
  };
})();
</script>

<?php endif; ?>
</body>
</html>
