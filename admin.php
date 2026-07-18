<?php
session_start();

$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;
$adminPassword = $config['admin_password'] ?? '';

require_once __DIR__ . '/categorias.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/VagaRepository.php';

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

$redirectTab = isset($_GET['tab']) && $_GET['tab'] !== 'lista' ? '&tab=' . urlencode($_GET['tab']) : '';
$redirectNs = isset($_GET['ns']) && in_array($_GET['ns'], ['ativa', 'inativa']) ? '&ns=' . urlencode($_GET['ns']) : '';
$redirectMostrar = isset($_GET['mostrar']) && $_GET['mostrar'] === 'todas' ? '&mostrar=todas' : '';

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_ids']) && !empty($_POST['batch_action'])) {
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
            if ($_POST['batch_action'] === 'remover') {
                $stmt = $pdo->prepare("UPDATE vagas SET revisada_em = NOW() WHERE id IN ($placeholders) AND revisada_em IS NULL");
                $stmt->execute($ids);
            } else {
                $targetStatus = $_POST['batch_action'] === 'ativar' ? 'ativa' : 'inativa';
                $stmt = $pdo->prepare("UPDATE vagas SET status = ? WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$targetStatus], $ids));
            }
        }
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_ids']) && !empty($_POST['batch_categorize'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $ids = array_map('intval', explode(',', $_POST['batch_ids']));
        $catSlugs = array_filter(array_map('trim', explode(',', $_POST['batch_categorize'] ?? '')));
        if (!empty($ids) && !empty($catSlugs)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmtVagas = $pdo->prepare("SELECT id FROM vagas WHERE id IN ($placeholders)");
            $stmtVagas->execute($ids);
            $validIds = $stmtVagas->fetchAll(PDO::FETCH_COLUMN);

            $stmtIns = $pdo->prepare("INSERT IGNORE INTO vaga_categorias (vaga_id, categoria_id) SELECT :vaga_id, id FROM categorias WHERE slug = :slug");
            foreach ($validIds as $vagaId) {
                foreach ($catSlugs as $slug) {
                    $stmtIns->execute([':vaga_id' => $vagaId, ':slug' => $slug]);
                }
            }
        }
    } catch (Exception $e) {}
    header('Location: admin.php?tab=novas' . (isset($_GET['mostrar']) && $_GET['mostrar'] === 'todas' ? '&mostrar=todas' : '') . (isset($_GET['ns']) && in_array($_GET['ns'], ['ativa', 'inativa']) ? '&ns=' . $_GET['ns'] : ''));
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_ids']) && !empty($_POST['batch_remove_cats'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $ids = array_map('intval', explode(',', $_POST['batch_ids']));
        $catSlugs = array_filter(array_map('trim', explode(',', $_POST['batch_remove_cats'] ?? '')));
        if (!empty($ids) && !empty($catSlugs)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $quoted = array_map(fn($s) => $pdo->quote($s), $catSlugs);
            $stmt = $pdo->prepare("DELETE vc FROM vaga_categorias vc JOIN categorias c ON c.id = vc.categoria_id WHERE vc.vaga_id IN ($placeholders) AND c.slug IN (" . implode(',', $quoted) . ")");
            $stmt->execute($ids);
        }
    } catch (Exception $e) {}
    header('Location: admin.php?tab=novas' . (isset($_GET['mostrar']) && $_GET['mostrar'] === 'todas' ? '&mostrar=todas' : '') . (isset($_GET['ns']) && in_array($_GET['ns'], ['ativa', 'inativa']) ? '&ns=' . $_GET['ns'] : ''));
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
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_vaga_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmt = $pdo->prepare("UPDATE vagas SET revisada_em = NOW() WHERE id = :id AND revisada_em IS NULL");
        $stmt->execute([':id' => (int)$_POST['remover_vaga_id']]);
    } catch (Exception $e) {}
    header('Location: admin.php?tab=novas' . (isset($_GET['mostrar']) && $_GET['mostrar'] === 'todas' ? '&mostrar=todas' : '') . (isset($_GET['ns']) && in_array($_GET['ns'], ['ativa', 'inativa']) ? '&ns=' . $_GET['ns'] : ''));
    exit;
}

$mensagemNovas = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['limpar_revisao'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->exec("UPDATE vagas SET revisada_em = NOW() WHERE created_at >= NOW() - INTERVAL 24 HOUR AND revisada_em IS NULL");
    } catch (Exception $e) {}
    header('Location: admin.php?tab=novas');
    exit;
}

$mensagemCadastro = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_vaga'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $vagaId = 'manual-' . time() . '-' . bin2hex(random_bytes(4));
        $titulo = trim($_POST['titulo'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');
        $localizacao = trim($_POST['localizacao'] ?? '');
        $modeloTrabalho = trim($_POST['modelo_trabalho'] ?? '');
        $urlVaga = trim($_POST['url_vaga'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $resumo = trim($_POST['resumo'] ?? '');
        $publicadoEm = date('Y-m-d H:i:s');
        $origem = $_POST['origem'] ?? 'nacional';
        $categoriasSlugs = $_POST['categorias'] ?? [];

        $primeiraArea = !empty($categoriasSlugs) ? $categoriasSlugs[0] : null;

        $stmt = $pdo->prepare("INSERT INTO vagas (vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, descricao, resumo, publicado_em, status, origem, area) VALUES (:id, :titulo, :empresa, :local, :modelo, :url, :desc, :resumo, :publicado, 'inativa', :origem, :area)");
        $stmt->execute([
            ':id'        => $vagaId,
            ':titulo'    => $titulo,
            ':empresa'   => $empresa,
            ':local'     => $localizacao ?: null,
            ':modelo'    => $modeloTrabalho ?: null,
            ':url'       => $urlVaga ?: null,
            ':desc'      => $descricao,
            ':resumo'    => $resumo,
            ':publicado' => $publicadoEm ?: null,
            ':origem'    => $origem,
            ':area'      => $primeiraArea,
        ]);

        $novoId = (int)$pdo->lastInsertId();
        if ($novoId > 0) {
            salvarTagsVagaPorSlugs($pdo, $novoId, $categoriasSlugs);
        }

        $mensagemCadastro = 'Vaga cadastrada com sucesso!';
    } catch (Exception $e) {
        $mensagemCadastro = 'Erro ao cadastrar: ' . $e->getMessage();
    }
}

$mensagemEdicao = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_vaga'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $id = (int)($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');
        $localizacao = trim($_POST['localizacao'] ?? '');
        $modeloTrabalho = trim($_POST['modelo_trabalho'] ?? '');
        $urlVaga = trim($_POST['url_vaga'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $resumo = trim($_POST['resumo'] ?? '');
        $origem = $_POST['origem'] ?? 'nacional';
        $status = $_POST['status'] ?? 'inativa';
        $categoriasSlugs = $_POST['categorias'] ?? [];

        $primeiraArea = !empty($categoriasSlugs) ? $categoriasSlugs[0] : null;

        $setParts = ['titulo = :titulo', 'empresa = :empresa', 'localizacao = :local', 'modelo_trabalho = :modelo', 'url_vaga = :url', 'descricao = :desc', 'resumo = :resumo', 'origem = :origem', 'area = :area', 'status = :status'];
        $params = [
            ':id'      => $id,
            ':titulo'  => $titulo,
            ':empresa' => $empresa,
            ':local'   => $localizacao ?: null,
            ':modelo'  => $modeloTrabalho ?: null,
            ':url'     => $urlVaga ?: null,
            ':desc'    => $descricao,
            ':resumo'  => $resumo,
            ':origem'  => $origem,
            ':area'    => $primeiraArea,
            ':status'  => $status,
        ];

        if (!isset($_POST['manter_data'])) {
            $publicadoEm = trim($_POST['publicado_em'] ?? '') ?: date('Y-m-d H:i:s');
            $setParts[] = 'publicado_em = :publicado';
            $params[':publicado'] = $publicadoEm;
        }

        $sql = "UPDATE vagas SET " . implode(', ', $setParts) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        salvarTagsVagaPorSlugs($pdo, $id, $categoriasSlugs);

        $mensagemEdicao = 'Vaga atualizada com sucesso!';
    } catch (Exception $e) {
        $mensagemEdicao = 'Erro ao atualizar: ' . $e->getMessage();
    }
}

$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['cadastro', 'editar', 'emails', 'blog', 'novas', 'categorias']) ? $_GET['tab'] : 'lista';

$blogMsg = '';
if ($isLoggedIn && $tab === 'blog' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_blog'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        setupSchema($pdo);
        $slug = $_POST['slug'] ?? '';
        $slug = preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', mb_strtolower(trim($slug))));
        if (!$slug) $slug = 'post-' . time();
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $author = trim($_POST['author'] ?? 'Mondywork');
        $status = $_POST['status'] ?? 'rascunho';
        $lang = $_POST['lang'] ?? 'pt';
        $image = trim($_POST['image'] ?? '');
        if (isset($_POST['remover_imagem'])) {
            $image = '';
        } elseif (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','gif','svg'])) {
                $dest = __DIR__ . '/uploads/blog/' . $slug . '-' . time() . '.' . $ext;
                $urlPath = '/uploads/blog/' . basename($dest);
                move_uploaded_file($_FILES['image_file']['tmp_name'], $dest);
                $image = $urlPath;
            }
        } elseif ($urlManual = trim($_POST['image_url'] ?? '')) {
            $image = $urlManual;
        }
        $categoria = trim($_POST['categoria'] ?? '');
        if (isset($_POST['id']) && $_POST['id']) {
            $stmt = $pdo->prepare("UPDATE blog_posts SET slug=:slug, title=:title, content=:content, excerpt=:excerpt, image=:image, categoria=:categoria, author=:author, status=:status, lang=:lang, published_at=IF(:status_check='publicado' AND published_at IS NULL, NOW(), published_at) WHERE id=:id");
            $stmt->execute([':slug' => $slug, ':title' => $title, ':content' => $content, ':excerpt' => $excerpt, ':image' => $image ?: null, ':categoria' => $categoria ?: null, ':author' => $author, ':status' => $status, ':lang' => $lang, ':status_check' => $status, ':id' => (int)$_POST['id']]);
            $blogMsg = 'Post atualizado com sucesso!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (slug, title, content, excerpt, image, categoria, author, status, lang, published_at) VALUES (:slug, :title, :content, :excerpt, :image, :categoria, :author, :status, :lang, IF(:status_check='publicado', NOW(), NULL))");
            $stmt->execute([':slug' => $slug, ':title' => $title, ':content' => $content, ':excerpt' => $excerpt, ':image' => $image ?: null, ':categoria' => $categoria ?: null, ':author' => $author, ':status' => $status, ':lang' => $lang, ':status_check' => $status]);
            $blogMsg = 'Post criado com sucesso!';
        }
    } catch (Exception $e) {
        $blogMsg = 'Erro: ' . $e->getMessage();
    }
}
if ($isLoggedIn && $tab === 'blog' && isset($_GET['delete'])) {
    try {
        $pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4", $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        setupSchema($pdo);
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id=:id");
        $stmt->execute([':id' => (int)$_GET['delete']]);
        $blogMsg = 'Post excluído.';
    } catch (Exception $e) {}
}

// ── Categoria CRUD ──
$mensagemCategoria = '';
$erroCategoria = '';
if ($isLoggedIn && $tab === 'categorias') {
    try {
        $pdoCat = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_categoria'])) {
            $nomePt = trim($_POST['nome_pt'] ?? '');
            $nomeEn = trim($_POST['nome_en'] ?? '');
            $slug = trim($_POST['slug'] ?? '');

            if ($nomePt === '' || $nomeEn === '' || $slug === '') {
                $erroCategoria = 'Preencha todos os campos.';
            } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                $erroCategoria = 'O slug deve conter apenas letras minúsculas, números e hífens.';
            } elseif (strlen($slug) > 30) {
                $erroCategoria = 'O slug deve ter no máximo 30 caracteres.';
            } else {
                $stmt = $pdoCat->prepare("SELECT COUNT(*) FROM categorias WHERE slug = :slug");
                $stmt->execute([':slug' => $slug]);
                if ($stmt->fetchColumn() > 0) {
                    $erroCategoria = 'Já existe uma categoria com este slug.';
                } else {
                    $stmt = $pdoCat->prepare("INSERT INTO categorias (slug, nome_pt, nome_en) VALUES (:slug, :pt, :en)");
                    $stmt->execute([':slug' => $slug, ':pt' => $nomePt, ':en' => $nomeEn]);
                    $mensagemCategoria = 'Categoria criada com sucesso!';
                }
            }
        }

        if (isset($_GET['deletar_categoria'])) {
            $catId = (int)$_GET['deletar_categoria'];
            if ($catId > 0) {
                $stmt = $pdoCat->prepare("SELECT COUNT(*) FROM vaga_categorias WHERE categoria_id = :id");
                $stmt->execute([':id' => $catId]);
                $vagasCount = (int)$stmt->fetchColumn();

                if ($vagasCount > 0) {
                    $erroCategoria = "Não é possível excluir: {$vagasCount} vaga(s) estão usando esta categoria. Remova a categoria das vagas primeiro.";
                } else {
                    $stmt = $pdoCat->prepare("DELETE FROM categorias WHERE id = :id AND slug != 'sem-categoria'");
                    $stmt->execute([':id' => $catId]);
                    if ($stmt->rowCount() > 0) {
                        $mensagemCategoria = 'Categoria excluída com sucesso!';
                    } else {
                        $erroCategoria = 'Não é possível excluir a categoria "Sem Categoria".';
                    }
                }
            }
        }

        $categoriasLista = $pdoCat->query("SELECT c.*, (SELECT COUNT(*) FROM vaga_categorias vc WHERE vc.categoria_id = c.id) as total_vagas FROM categorias c ORDER BY c.nome_pt")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $erroCategoria = 'Erro ao acessar o banco de dados.';
        $categoriasLista = [];
    }
}

$origemFilter = isset($_GET['origem']) && in_array($_GET['origem'], ['nacional', 'exterior']) ? $_GET['origem'] : '';
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['ativa', 'inativa']) ? $_GET['status'] : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

$catFilter = isset($_GET['categorias']) && is_array($_GET['categorias']) ? $_GET['categorias'] : [];
$semCategoria = in_array('sem-categoria', $catFilter);
$catSlugs = array_values(array_filter($catFilter, fn($s) => $s !== 'sem-categoria'));

$tabParam = $tab !== 'lista' ? '&tab=' . $tab : '';
$qParam = $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '';
$origemParam = $origemFilter !== '' ? '&origem=' . urlencode($origemFilter) : '';
$statusParam = $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '';
$categoriasParam = '';
foreach ($catFilter as $slug) {
    $categoriasParam .= '&categorias[]=' . urlencode($slug);
}
$pageParam = isset($_GET['page']) ? '&page=' . (int)$_GET['page'] : '';

// ── CSV Export ──
if ($isLoggedIn && $tab === 'emails' && isset($_GET['export']) && $_GET['export'] === 'csv') {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmt = $pdo->query("SELECT nome, email, area, origem, created_at FROM newsletters ORDER BY created_at DESC");
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="newsletters-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Nome', 'Email', 'Area', 'Origem', 'Data de Cadastro']);
        foreach ($emails as $row) {
            fputcsv($output, [$row['nome'], $row['email'], $row['area'], $row['origem'], $row['created_at']]);
        }
        fclose($output);
        exit;
    } catch (Exception $e) {
        // fallback: continua para pagina normal
    }
}

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Mondywork</title>
<link rel="stylesheet" href="/css/style.css?v=1.8.0">
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
.admin-card-title { font-size: 18px; line-height: 24px; font-weight: 600; color: #0b1c30; word-break: break-word; }
.admin-card-company { font-size: 13px; line-height: 18px; font-weight: 500; color: #45464d; margin-top: 2px; word-break: break-word; }
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
.admin-tabs { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 24px; border-bottom: 1px solid #c6c6cd; padding-bottom: 0; }
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
.admin-search { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-search-input { flex: 1; min-width: 200px; background: #fff; border: 1px solid #c6c6cd; border-radius: 0.5rem; padding: 10px 16px; font-size: 14px; color: #0b1c30; outline: none; transition: border-color 0.2s; }
.admin-search-input:focus { border-color: #4b41e1; box-shadow: 0 0 0 1px #4b41e1; }
.btn-search { background: #4b41e1; color: #fff; font-size: 13px; font-weight: 600; padding: 10px 20px; border: none; border-radius: 0.5rem; cursor: pointer; transition: background 0.3s; }
.btn-search:hover { background: #645efb; }
.btn-clear { display: inline-flex; align-items: center; font-size: 13px; font-weight: 500; color: #45464d; padding: 10px 16px; border: 1px solid #c6c6cd; border-radius: 0.5rem; text-decoration: none; transition: all 0.2s; }
.btn-clear:hover { border-color: #ba1a1a; color: #ba1a1a; }
.cat-checkboxes { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.cat-checkbox { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #0b1c30; cursor: pointer; padding: 6px 12px; border: 1px solid #c6c6cd; border-radius: 0.5rem; background: #fff; transition: all 0.2s; }
.cat-checkbox:hover { border-color: #4b41e1; }
.cat-checkbox input { width: 16px; height: 16px; cursor: pointer; }
.cat-filter { background: #fff; border: 1px solid #c6c6cd; border-radius: 0.5rem; margin-bottom: 16px; overflow: hidden; }
.cat-filter-summary { padding: 10px 16px; font-size: 14px; font-weight: 600; cursor: pointer; color: #0b1c30; user-select: none; }
.cat-filter-summary:hover { background: #f8f9fa; }
.cat-filter-form { padding: 0 16px 16px; border-top: 1px solid #c6c6cd; padding-top: 12px; }
.cat-filter-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.cat-filter-checkbox { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #0b1c30; cursor: pointer; padding: 6px 12px; border: 1px solid #c6c6cd; border-radius: 0.5rem; background: #f8f9fa; transition: all 0.2s; }
.cat-filter-checkbox:hover { border-color: #4b41e1; }
.cat-filter-checkbox input { width: 16px; height: 16px; cursor: pointer; }
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

    setupSchema($pdo);

    $whereClauses = [];
    if ($origemFilter !== '') $whereClauses[] = "origem = " . $pdo->quote($origemFilter);
    if ($statusFilter !== '') $whereClauses[] = "status = " . $pdo->quote($statusFilter);
    if ($searchQuery !== '') {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $searchQuery);
        $like = $pdo->quote('%' . $escaped . '%');
        $whereClauses[] = "(titulo LIKE $like OR empresa LIKE $like OR localizacao LIKE $like)";
    }
    if (!empty($catSlugs)) {
        $quoted = array_map(fn($s) => $pdo->quote($s), $catSlugs);
        $whereClauses[] = "EXISTS (SELECT 1 FROM vaga_categorias vc JOIN categorias c ON c.id = vc.categoria_id WHERE vc.vaga_id = vagas.id AND c.slug IN (" . implode(',', $quoted) . "))";
    }
    if ($semCategoria) {
        $whereClauses[] = "NOT EXISTS (SELECT 1 FROM vaga_categorias WHERE vaga_id = vagas.id)";
    }
    $where = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : '';
    $whereStatusAtiva = " WHERE status = 'ativa'" . (!empty($whereClauses) ? " AND " . implode(" AND ", $whereClauses) : '');
    $whereStatusInativa = " WHERE status = 'inativa'" . (!empty($whereClauses) ? " AND " . implode(" AND ", $whereClauses) : '');

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 30;
    $offset = ($page - 1) * $limit;

    $totalStmt = $pdo->query("SELECT COUNT(*) FROM vagas" . $where);
    $totalVagas = (int)$totalStmt->fetchColumn();

    $ativasStmt = $pdo->query("SELECT COUNT(*) FROM vagas" . $whereStatusAtiva);
    $totalAtivas = (int)$ativasStmt->fetchColumn();

    $inativasStmt = $pdo->query("SELECT COUNT(*) FROM vagas" . $whereStatusInativa);
    $totalInativas = (int)$inativasStmt->fetchColumn();

    $totalPages = $totalVagas > 0 ? (int)ceil($totalVagas / $limit) : 1;

    $stmt = $pdo->prepare("SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, vagas.modelo_trabalho, vagas.descricao, vagas.resumo, vagas.status, vagas.origem, vagas.area, vagas.publicado_em, DATE_FORMAT(vagas.publicado_em, '%d/%m/%Y') as publicado_em_fmt, GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str FROM vagas LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id LEFT JOIN categorias c ON c.id = vc.categoria_id" . $where . " GROUP BY vagas.id ORDER BY vagas.publicado_em DESC, vagas.data_coleta DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $todasCategorias = $pdo->query("SELECT * FROM categorias ORDER BY nome_pt")->fetchAll(PDO::FETCH_ASSOC);

    $newsletters = [];
    $totalNewsletters = 0;
    if ($tab === 'emails') {
        $pageNews = max(1, (int)($_GET['page'] ?? 1));
        $limitNews = 50;
        $offsetNews = ($pageNews - 1) * $limitNews;
        $totalNewsletters = (int)$pdo->query("SELECT COUNT(*) FROM newsletters")->fetchColumn();
        $totalNewsPages = max(1, (int)ceil($totalNewsletters / $limitNews));
        $stmtNews = $pdo->prepare("SELECT * FROM newsletters ORDER BY created_at DESC LIMIT :lim OFFSET :off");
        $stmtNews->bindValue(':lim', $limitNews, PDO::PARAM_INT);
        $stmtNews->bindValue(':off', $offsetNews, PDO::PARAM_INT);
        $stmtNews->execute();
        $newsletters = $stmtNews->fetchAll(PDO::FETCH_ASSOC);
    }

    $vagaEditar = null;
    $categoriasVaga = [];
    if ($tab === 'editar' && isset($_GET['id'])) {
        $editId = (int)$_GET['id'];
        $stmtEdit = $pdo->prepare("SELECT * FROM vagas WHERE id = :id LIMIT 1");
        $stmtEdit->execute([':id' => $editId]);
        $vagaEditar = $stmtEdit->fetch(PDO::FETCH_ASSOC);
        if (!$vagaEditar) {
            $mensagemEdicao = 'Vaga não encontrada.';
            $tab = 'lista';
        } else {
            $stmtCat = $pdo->prepare("SELECT c.slug FROM vaga_categorias vc JOIN categorias c ON c.id = vc.categoria_id WHERE vc.vaga_id = :id");
            $stmtCat->execute([':id' => $editId]);
            $categoriasVaga = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
        }
    }

} catch (Exception $e) {
    echo '<div class="admin-main"><p style="color:#ba1a1a;">Erro ao conectar ao banco de dados.</p></div>';
    exit;
}
?>

<main class="admin-main">
  <div class="admin-tabs">
    <a class="admin-tab <?php echo $tab === 'lista' ? 'active' : '' ?>" href="admin.php">Vagas</a>
    <a class="admin-tab <?php echo $tab === 'cadastro' ? 'active' : '' ?>" href="admin.php?tab=cadastro">Cadastrar Vaga</a>
    <a class="admin-tab <?php echo $origemFilter === 'nacional' ? 'active' : '' ?>" href="admin.php?origem=nacional<?php echo $qParam . $statusParam ?>">Brasil</a>
    <a class="admin-tab <?php echo $origemFilter === 'exterior' ? 'active' : '' ?>" href="admin.php?origem=exterior<?php echo $qParam . $statusParam ?>">Exterior</a>
    <span style="flex:1"></span>
    <a class="admin-tab <?php echo $statusFilter === '' ? 'active' : '' ?>" href="admin.php?<?php echo $origemParam . $qParam ?>">Todas</a>
    <a class="admin-tab <?php echo $statusFilter === 'ativa' ? 'active' : '' ?>" href="admin.php?status=ativa<?php echo $origemParam . $qParam ?>">Ativas</a>
    <a class="admin-tab <?php echo $statusFilter === 'inativa' ? 'active' : '' ?>" href="admin.php?status=inativa<?php echo $origemParam . $qParam ?>">Inativas</a>
    <span style="flex:1"></span>
    <a class="admin-tab <?php echo $tab === 'emails' ? 'active' : '' ?>" href="admin.php?tab=emails">Emails</a>
    <a class="admin-tab <?php echo $tab === 'novas' ? 'active' : '' ?>" href="admin.php?tab=novas">Novas (24h)</a>
    <a class="admin-tab <?php echo $tab === 'blog' ? 'active' : '' ?>" href="admin.php?tab=blog">Blog</a>
    <a class="admin-tab <?php echo $tab === 'categorias' ? 'active' : '' ?>" href="admin.php?tab=categorias">Categorias</a>
  </div>

  <?php if ($tab === 'cadastro'): ?>

  <div class="admin-header">
    <div>
      <h1>Cadastrar Vaga</h1>
      <span>Preencha os campos para criar uma nova vaga manualmente</span>
    </div>
  </div>

  <?php if ($mensagemCadastro): ?>
    <div class="admin-card" style="margin-bottom:16px;padding:16px 24px;border-left:4px solid <?php echo str_starts_with($mensagemCadastro, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>">
      <p style="font-weight:600;margin:0;color:<?php echo str_starts_with($mensagemCadastro, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>"><?php echo htmlspecialchars($mensagemCadastro, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <form method="post" style="display:flex;flex-direction:column;gap:16px">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Título *</label>
          <input type="text" name="titulo" required class="admin-search-input" style="width:100%">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Empresa *</label>
          <input type="text" name="empresa" required class="admin-search-input" style="width:100%">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Localização</label>
          <input type="text" name="localizacao" class="admin-search-input" style="width:100%" placeholder="Ex: São Paulo, SP">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Modelo de Trabalho</label>
          <select name="modelo_trabalho" class="admin-search-input" style="width:100%">
            <option value="">Selecione</option>
            <option value="Remote">Remoto</option>
            <option value="Hybrid">Híbrido</option>
            <option value="On-site">Presencial</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">URL da Vaga</label>
          <input type="url" name="url_vaga" class="admin-search-input" style="width:100%" placeholder="https://...">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Origem *</label>
          <select name="origem" required class="admin-search-input" style="width:100%">
            <option value="nacional">Brasil</option>
            <option value="exterior">Exterior</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Categorias (tags)</label>
          <div class="cat-checkboxes">
            <?php foreach ($todasCategorias as $cat): ?>
              <label class="cat-checkbox">
                <input type="checkbox" name="categorias[]" value="<?php echo $cat['slug'] ?>">
                <span><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Resumo</label>
        <textarea name="resumo" class="admin-search-input" style="width:100%;min-height:60px;resize:vertical" placeholder="Breve resumo da vaga..."></textarea>
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Descrição (HTML)</label>
        <textarea name="descricao" class="admin-search-input" style="width:100%;min-height:200px;resize:vertical;font-family:monospace" placeholder="<p>Descrição da vaga em HTML...</p>"></textarea>
      </div>
      <div>
        <button type="submit" name="cadastrar_vaga" class="btn-search" style="font-size:15px;padding:12px 32px">Cadastrar Vaga</button>
      </div>
    </form>
  </div>

  <?php elseif ($tab === 'editar' && $vagaEditar): ?>

  <div class="admin-header">
    <div>
      <h1>Editar Vaga</h1>
      <span>ID: <?php echo (int)$vagaEditar['id'] ?> — <?php echo htmlspecialchars($vagaEditar['vaga_id_externo'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <a href="admin.php<?php echo $origemParam . $statusParam . $qParam ?>" class="btn-clear" style="font-size:13px">&larr; Voltar</a>
  </div>

  <?php if ($mensagemEdicao): ?>
    <div class="admin-card" style="margin-bottom:16px;padding:16px 24px;border-left:4px solid <?php echo str_starts_with($mensagemEdicao, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>">
      <p style="font-weight:600;margin:0;color:<?php echo str_starts_with($mensagemEdicao, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>"><?php echo htmlspecialchars($mensagemEdicao, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <form method="post" style="display:flex;flex-direction:column;gap:16px">
      <input type="hidden" name="id" value="<?php echo (int)$vagaEditar['id'] ?>">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Título *</label>
          <input type="text" name="titulo" required class="admin-search-input" style="width:100%" value="<?php echo htmlspecialchars($vagaEditar['titulo'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Empresa *</label>
          <input type="text" name="empresa" required class="admin-search-input" style="width:100%" value="<?php echo htmlspecialchars($vagaEditar['empresa'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Localização</label>
          <input type="text" name="localizacao" class="admin-search-input" style="width:100%" placeholder="Ex: São Paulo, SP" value="<?php echo htmlspecialchars($vagaEditar['localizacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Modelo de Trabalho</label>
          <select name="modelo_trabalho" class="admin-search-input" style="width:100%">
            <option value="">Selecione</option>
            <option value="Remote" <?php echo $vagaEditar['modelo_trabalho'] === 'Remote' ? 'selected' : '' ?>>Remoto</option>
            <option value="Hybrid" <?php echo $vagaEditar['modelo_trabalho'] === 'Hybrid' ? 'selected' : '' ?>>Híbrido</option>
            <option value="On-site" <?php echo $vagaEditar['modelo_trabalho'] === 'On-site' ? 'selected' : '' ?>>Presencial</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">URL da Vaga</label>
          <input type="url" name="url_vaga" class="admin-search-input" style="width:100%" placeholder="https://..." value="<?php echo htmlspecialchars($vagaEditar['url_vaga'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Publicado em</label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#45464d;cursor:pointer;margin-bottom:8px">
            <input type="checkbox" name="manter_data" id="manter_data" checked>
            Manter a data original
          </label>
          <input type="date" name="publicado_em" id="campo_data" class="admin-search-input" style="width:100%;display:none" value="<?php echo $vagaEditar['publicado_em'] ? date('Y-m-d', strtotime($vagaEditar['publicado_em'])) : '' ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Origem *</label>
          <select name="origem" required class="admin-search-input" style="width:100%">
            <option value="nacional" <?php echo $vagaEditar['origem'] === 'nacional' ? 'selected' : '' ?>>Brasil</option>
            <option value="exterior" <?php echo $vagaEditar['origem'] === 'exterior' ? 'selected' : '' ?>>Exterior</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Categorias (tags)</label>
          <div class="cat-checkboxes">
            <?php foreach ($todasCategorias as $cat): ?>
              <label class="cat-checkbox">
                <input type="checkbox" name="categorias[]" value="<?php echo $cat['slug'] ?>" <?php echo in_array($cat['slug'], $categoriasVaga) ? 'checked' : '' ?>>
                <span><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Status</label>
          <select name="status" class="admin-search-input" style="width:100%">
            <option value="ativa" <?php echo $vagaEditar['status'] === 'ativa' ? 'selected' : '' ?>>Ativa</option>
            <option value="inativa" <?php echo $vagaEditar['status'] === 'inativa' ? 'selected' : '' ?>>Inativa</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Resumo</label>
          <textarea name="resumo" class="admin-search-input" style="width:100%;min-height:60px;resize:vertical" placeholder="Breve resumo da vaga..."><?php echo htmlspecialchars($vagaEditar['resumo'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Descrição (HTML)</label>
        <textarea name="descricao" class="admin-search-input" style="width:100%;min-height:200px;resize:vertical;font-family:monospace"><?php echo htmlspecialchars($vagaEditar['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" name="editar_vaga" class="btn-search" style="font-size:15px;padding:12px 32px">Salvar Alterações</button>
        <a href="admin.php<?php echo $origemParam . $statusParam . $qParam . $categoriasParam ?>" class="btn-clear" style="display:inline-flex;align-items:center">Cancelar</a>
      </div>
    </form>
  </div>

  <?php elseif ($tab === 'emails'): ?>

  <div class="admin-header">
    <div>
      <h1>Emails Cadastrados</h1>
      <span><?php echo $totalNewsletters ?> inscritos no total</span>
    </div>
    <a href="?tab=emails&export=csv" class="btn-search" style="text-decoration:none;font-size:14px;padding:10px 24px">Exportar CSV</a>
  </div>

  <?php if (empty($newsletters)): ?>
    <div class="admin-empty">Nenhum email cadastrado.</div>
  <?php else: ?>
    <div style="overflow-x:auto;background:#fff;border-radius:0.75rem;border:1px solid #c6c6cd">
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
          <tr style="background:#f8f9fa;border-bottom:2px solid #c6c6cd">
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Nome</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Email</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Área</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Origem</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Cadastro</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($newsletters as $n): ?>
            <tr style="border-bottom:1px solid #e5e7eb">
              <td style="padding:10px 16px;font-weight:600;color:#0b1c30"><?php echo htmlspecialchars($n['nome'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px;color:#4b41e1"><?php echo htmlspecialchars($n['email'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px;color:#45464d"><?php echo htmlspecialchars($n['area'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px"><span class="badge-origem <?php echo $n['origem'] === 'exterior' ? 'exterior' : '' ?>"><?php echo $n['origem'] === 'exterior' ? 'Exterior' : 'Brasil' ?></span></td>
              <td style="padding:10px 16px;color:#45464d"><?php echo date('d/m/Y H:i', strtotime($n['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($totalNewsPages > 1): ?>
      <div class="admin-pagination">
        <?php if ($pageNews > 1): ?>
          <a href="?tab=emails&page=<?php echo $pageNews - 1 ?>">&laquo;</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalNewsPages; $i++): ?>
          <?php if ($i === $pageNews): ?>
            <span class="current"><?php echo $i ?></span>
          <?php else: ?>
            <a href="?tab=emails&page=<?php echo $i ?>"><?php echo $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pageNews < $totalNewsPages): ?>
          <a href="?tab=emails&page=<?php echo $pageNews + 1 ?>">&raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <?php elseif ($tab === 'novas'):

  $novasStatusFilter = isset($_GET['ns']) && in_array($_GET['ns'], ['ativa', 'inativa']) ? $_GET['ns'] : '';
  $mostrarTodas = isset($_GET['mostrar']) && $_GET['mostrar'] === 'todas';
  $novasWhere = "created_at >= NOW() - INTERVAL 24 HOUR";
  if (!$mostrarTodas) {
      $novasWhere .= " AND revisada_em IS NULL";
  }
  if ($novasStatusFilter !== '') {
      $novasWhere .= " AND status = " . $pdo->quote($novasStatusFilter);
  }

  $stmtNovas = $pdo->query("SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, vagas.modelo_trabalho, vagas.descricao, vagas.resumo, vagas.status, vagas.origem, vagas.publicado_em, vagas.created_at, DATE_FORMAT(vagas.created_at, '%d/%m/%Y %H:%i') as created_at_fmt, GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str FROM vagas LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id LEFT JOIN categorias c ON c.id = vc.categoria_id WHERE {$novasWhere} GROUP BY vagas.id ORDER BY vagas.created_at DESC");
  $novasVagas = $stmtNovas->fetchAll(PDO::FETCH_ASSOC);

  $totalNovas = count($novasVagas);
  $novasAtivas = count(array_filter($novasVagas, fn($v) => $v['status'] === 'ativa'));
  $novasInativas = count(array_filter($novasVagas, fn($v) => $v['status'] === 'inativa'));

  $nsParam = $novasStatusFilter !== '' ? '&ns=' . $novasStatusFilter : '';
  $mostrarParam = !$mostrarTodas ? '' : '&mostrar=todas';
  ?>

  <div class="admin-header">
    <div>
      <h1>Vagas Recentes (últimas 24h)</h1>
      <span><?php echo $totalNovas ?> vagas encontradas</span>
    </div>
    <form method="post" style="margin:0" onsubmit="return confirm('Limpar a lista atual? As vagas desta leva serão marcadas como revisadas e não aparecerão mais aqui.')">
      <button type="submit" name="limpar_revisao" class="btn-toggle inativar" style="padding:10px 24px">Limpar lista</button>
    </form>
  </div>

  <?php if ($mensagemNovas): ?>
    <div style="background:#e6f7e6;border:1px solid #b3e6b3;border-radius:0.5rem;padding:12px 16px;margin-bottom:16px;font-size:14px;font-weight:500;color:#1a7d1a"><?php echo htmlspecialchars($mensagemNovas, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="admin-stats">
    <div class="stat">Novas: <strong><?php echo $totalNovas ?></strong></div>
    <div class="stat">Ativas: <strong style="color:#1a7d1a"><?php echo $novasAtivas ?></strong></div>
    <div class="stat">Inativas: <strong style="color:#ba1a1a"><?php echo $novasInativas ?></strong></div>
  </div>

  <div class="admin-tabs" style="margin-bottom:16px">
    <a class="admin-tab <?php echo !$mostrarTodas ? 'active' : '' ?>" href="?tab=novas<?php echo $nsParam ?>">Não revisadas</a>
    <a class="admin-tab <?php echo $mostrarTodas ? 'active' : '' ?>" href="?tab=novas&mostrar=todas<?php echo $nsParam ?>">Todas</a>
    <span style="flex:1"></span>
    <a class="admin-tab <?php echo $novasStatusFilter === '' ? 'active' : '' ?>" href="?tab=novas<?php echo $mostrarParam ?>">Status: Todas</a>
    <a class="admin-tab <?php echo $novasStatusFilter === 'ativa' ? 'active' : '' ?>" href="?tab=novas&ns=ativa<?php echo $mostrarParam ?>">Ativas</a>
    <a class="admin-tab <?php echo $novasStatusFilter === 'inativa' ? 'active' : '' ?>" href="?tab=novas&ns=inativa<?php echo $mostrarParam ?>">Inativas</a>
  </div>

  <div class="batch-bar" id="batch-bar-top"></div>

  <?php if (empty($novasVagas)): ?>
    <div class="admin-empty"><?php echo $mostrarTodas ? 'Nenhuma vaga nova nas últimas 24 horas.' : 'Lista vazia! Todas as vagas recentes foram revisadas. As próximas vagas do sync.php aparecerão aqui automaticamente.' ?></div>
  <?php else:
    foreach ($novasVagas as $v): ?>
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
            <span><?php echo htmlspecialchars($v['modelo_trabalho'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <span class="badge-status <?php echo $v['status'] ?>"><?php echo $v['status'] === 'ativa' ? 'Ativa' : 'Inativa' ?></span>
          <span style="font-size:12px;color:#45464d">Inserida: <?php echo htmlspecialchars($v['created_at_fmt'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php if (!empty($v['tags_str'])): ?>
          <div style="display:flex;flex-wrap:wrap;gap:4px;margin:6px 0 2px">
            <?php foreach (explode(', ', $v['tags_str']) as $tag): ?>
              <span style="font-size:11px;padding:2px 8px;border-radius:999px;background:#e8e8ff;color:#4b41e1;white-space:nowrap"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="admin-card-actions">
          <a href="admin.php?tab=editar&id=<?php echo (int)$v['id'] ?>" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #4b41e1;color:#4b41e1;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Editar</a>
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle <?php echo $v['status'] === 'ativa' ? 'inativar' : 'ativar' ?>"><?php echo $v['status'] === 'ativa' ? 'Inativar' : 'Ativar' ?></button>
          </form>
          <?php if ($v['vaga_id_externo']): ?>
            <a href="/#<?php echo urlencode($v['vaga_id_externo']) ?>" target="_blank" style="font-size:13px;font-weight:500;color:#4b41e1;padding:7px 18px;border:1px solid #4b41e1;border-radius:0.5rem;text-decoration:none;display:inline-flex;align-items:center">Ver no site</a>
          <?php endif; ?>
          <form method="post" style="margin:0" onsubmit="return confirm('Remover esta vaga da lista de novas?')">
            <input type="hidden" name="remover_vaga_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle inativar" style="font-size:13px">Remover</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="batch-bar" id="batch-bar-bottom"></div>

  <?php elseif ($tab === 'blog'): ?>
  <?php
  $blogLangFilter = isset($_GET['blang']) && in_array($_GET['blang'], ['pt', 'en']) ? $_GET['blang'] : '';
  $blangParam = $blogLangFilter ? '&blang=' . urlencode($blogLangFilter) : '';
  ?>

  <div class="admin-header">
    <div>
      <h1>Blog</h1>
      <span>Gerenciar posts do blog</span>
    </div>
    <a href="?tab=blog&editar=novo<?php echo $blangParam ?>" class="btn-search" style="text-decoration:none;font-size:14px;padding:10px 24px">+ Novo Post</a>
  </div>

  <div class="admin-tabs" style="margin-bottom: 20px;">
    <a class="admin-tab <?php echo $blogLangFilter === '' ? 'active' : '' ?>" href="?tab=blog">Todos os Idiomas</a>
    <a class="admin-tab <?php echo $blogLangFilter === 'pt' ? 'active' : '' ?>" href="?tab=blog&blang=pt">Português (PT)</a>
    <a class="admin-tab <?php echo $blogLangFilter === 'en' ? 'active' : '' ?>" href="?tab=blog&blang=en">Inglês (EN)</a>
  </div>

  <?php if ($blogMsg): ?>
    <div class="admin-card" style="margin-bottom:16px;padding:16px 24px;border-left:4px solid <?php echo str_starts_with($blogMsg, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>">
      <p style="font-weight:600;margin:0;color:<?php echo str_starts_with($blogMsg, 'Erro') ? '#ba1a1a' : '#1a7d1a' ?>"><?php echo htmlspecialchars($blogMsg, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <?php
  $editarPostId = isset($_GET['editar']) && $_GET['editar'] !== 'novo' ? (int)$_GET['editar'] : null;
  $isNewPost = isset($_GET['editar']) && $_GET['editar'] === 'novo';

  if ($isNewPost || $editarPostId):
    $post = null;
    if ($editarPostId) {
      $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id=:id");
      $stmt->execute([':id' => $editarPostId]);
      $post = $stmt->fetch(PDO::FETCH_ASSOC);
    }
  ?>

  <div class="admin-card">
    <form method="post" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:16px">
      <?php if ($post): ?>
        <input type="hidden" name="id" value="<?php echo (int)$post['id'] ?>">
      <?php endif; ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Título</label>
          <input type="text" name="title" id="post-title" required class="admin-search-input" style="width:100%" value="<?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="autoSlug(this.value)">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Slug (URL)</label>
          <input type="text" name="slug" id="post-slug" class="admin-search-input" style="width:100%" placeholder="ex: como-conseguir-emprego" value="<?php echo htmlspecialchars($post['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <script>
        var slugEdited = false;
        document.getElementById('post-slug').addEventListener('input', function() { slugEdited = true; });
        function autoSlug(title) {
          if (slugEdited) return;
          document.getElementById('post-slug').value = title
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        }
        <?php if (empty($post['slug'])): ?>
        slugEdited = false;
        <?php else: ?>
        slugEdited = true;
        <?php endif; ?>
        </script>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Autor</label>
          <input type="text" name="author" class="admin-search-input" style="width:100%" value="<?php echo htmlspecialchars($post['author'] ?? 'Mondywork', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Status</label>
          <select name="status" class="admin-search-input" style="width:100%">
            <option value="rascunho" <?php echo ($post['status'] ?? '') === 'rascunho' ? 'selected' : '' ?>>Rascunho</option>
            <option value="publicado" <?php echo ($post['status'] ?? '') === 'publicado' ? 'selected' : '' ?>>Publicado</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Imagem</label>
          <?php if (!empty($post['image'])): ?>
            <div style="margin-bottom:6px">
              <img src="<?php echo htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8') ?>" style="max-width:200px;max-height:120px;border-radius:6px;object-fit:cover">
            </div>
          <?php endif; ?>
          <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="admin-search-input" style="padding:8px">
          <input type="hidden" name="image" value="<?php echo htmlspecialchars($post['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <span style="font-size:12px;color:#45464d">Formatos: JPG, PNG, WebP, GIF, SVG. Ou informe uma URL externa abaixo.</span>
          <input type="url" name="image_url" class="admin-search-input" style="width:100%;margin-top:6px" placeholder="https://..." value="">
          <?php if (!empty($post['image'])): ?>
            <label style="display:flex;align-items:center;gap:6px;margin-top:6px;font-size:13px;color:#ba1a1a;cursor:pointer">
              <input type="checkbox" name="remover_imagem" value="1"> Remover imagem atual
            </label>
          <?php endif; ?>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Categoria</label>
          <select name="categoria" class="admin-search-input" style="width:100%">
            <option value="">Sem categoria</option>
            <?php foreach (['Carreira','Tecnologia','Design','Marketing','Produto','Comunicacao','Administracao','Financas','Dados','Mercado de Trabalho','Dicas'] as $cat): ?>
              <option value="<?php echo $cat ?>" <?php echo ($post['categoria'] ?? '') === $cat ? 'selected' : '' ?>><?php echo $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Idioma</label>
          <select name="lang" class="admin-search-input" style="width:100%">
            <option value="pt" <?php echo ($post['lang'] ?? 'pt') === 'pt' ? 'selected' : '' ?>>Português (pt)</option>
            <option value="en" <?php echo ($post['lang'] ?? 'pt') === 'en' ? 'selected' : '' ?>>Inglês (en)</option>
          </select>
        </div>
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Resumo</label>
        <textarea name="excerpt" class="admin-search-input" id="excerpt-counter" style="width:100%;min-height:80px;resize:vertical" placeholder="Resumo curto do post..." oninput="document.getElementById('excerpt-count').textContent=this.value.length"><?php echo htmlspecialchars($post['excerpt'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        <div style="font-size:12px;color:#76777d;margin-top:4px;text-align:right"><span id="excerpt-count"><?php echo mb_strlen($post['excerpt'] ?? '') ?></span> caracteres</div>
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Conteúdo — HTML</label>
        <textarea name="content" class="admin-search-input" style="width:100%;min-height:300px;resize:vertical;font-family:monospace"><?php echo htmlspecialchars($post['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" name="salvar_blog" class="btn-search" style="font-size:15px;padding:12px 32px"><?php echo $post ? 'Salvar Alterações' : 'Criar Post' ?></button>
        <a href="?tab=blog<?php echo $blangParam ?>" class="btn-clear" style="display:inline-flex;align-items:center">Cancelar</a>
      </div>
    </form>
  </div>

  <?php else:
    $pageBlog = max(1, (int)($_GET['bp'] ?? 1));
    $limitBlog = 20;
    $offsetBlog = ($pageBlog - 1) * $limitBlog;
    
    $whereBlog = "1=1";
    $paramsBlog = [];
    if ($blogLangFilter) {
        $whereBlog .= " AND lang = :lang";
        $paramsBlog[':lang'] = $blogLangFilter;
    }
    
    $totalBlogStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE $whereBlog");
    $totalBlogStmt->execute($paramsBlog);
    $totalBlog = (int)$totalBlogStmt->fetchColumn();
    $totalBlogPages = max(1, (int)ceil($totalBlog / $limitBlog));
    
    $stmtBlog = $pdo->prepare("SELECT id, slug, title, author, status, published_at, created_at, categoria, lang FROM blog_posts WHERE $whereBlog ORDER BY created_at DESC LIMIT :lim OFFSET :off");
    foreach ($paramsBlog as $k => $v) $stmtBlog->bindValue($k, $v);
    $stmtBlog->bindValue(':lim', $limitBlog, PDO::PARAM_INT);
    $stmtBlog->bindValue(':off', $offsetBlog, PDO::PARAM_INT);
    $stmtBlog->execute();
    $blogPosts = $stmtBlog->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <?php if (empty($blogPosts)): ?>
    <div class="admin-empty">Nenhum post ainda. <a href="?tab=blog&editar=novo<?php echo $blangParam ?>" style="color:#4b41e1">Criar primeiro post</a></div>
  <?php else: ?>
    <?php foreach ($blogPosts as $bp): ?>
      <div class="admin-card" style="margin-bottom:12px">
        <div class="admin-card-header">
          <div style="flex:1">
            <div class="admin-card-title"><?php echo htmlspecialchars($bp['title'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-card-company">/blog/<?php echo htmlspecialchars($bp['slug'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
        <div class="admin-card-meta">
          <span class="badge-status <?php echo $bp['status'] ?>"><?php echo $bp['status'] === 'publicado' ? 'Publicado' : 'Rascunho' ?></span>
          <span style="font-size:12px;font-weight:600;color:#0b1c30;text-transform:uppercase;background:#e2e8f0;padding:2px 6px;border-radius:4px"><?php echo htmlspecialchars($bp['lang'] ?? 'pt', ENT_QUOTES, 'UTF-8') ?></span>
          <?php if (!empty($bp['categoria'])): ?>
            <span style="font-size:12px;color:#4b41e1;font-weight:600"><?php echo htmlspecialchars($bp['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <span style="font-size:12px;color:#45464d"><?php echo $bp['author'] ?> • <?php echo $bp['published_at'] ? date('d/m/Y', strtotime($bp['published_at'])) : '—' ?></span>
        </div>
        <div class="admin-card-actions">
          <a href="?tab=blog&editar=<?php echo (int)$bp['id'] ?>" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #4b41e1;color:#4b41e1;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Editar</a>
          <a href="?tab=blog&delete=<?php echo (int)$bp['id'] ?>" onclick="return confirm('Excluir este post?')" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #ba1a1a;color:#ba1a1a;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Excluir</a>
          <?php if ($bp['status'] === 'publicado'): ?>
            <a href="/blog/<?php echo urlencode($bp['slug']) ?>" target="_blank" style="font-size:13px;font-weight:500;color:#4b41e1;padding:7px 18px;border:1px solid #4b41e1;border-radius:0.5rem;text-decoration:none;display:inline-flex;align-items:center">Ver</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if ($totalBlogPages > 1): ?>
      <div class="admin-pagination">
        <?php if ($pageBlog > 1): ?>
          <a href="?tab=blog<?php echo $blangParam ?>&bp=<?php echo $pageBlog - 1 ?>">&laquo;</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalBlogPages; $i++): ?>
          <?php if ($i === $pageBlog): ?>
            <span class="current"><?php echo $i ?></span>
          <?php else: ?>
            <a href="?tab=blog<?php echo $blangParam ?>&bp=<?php echo $i ?>"><?php echo $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pageBlog < $totalBlogPages): ?>
          <a href="?tab=blog<?php echo $blangParam ?>&bp=<?php echo $pageBlog + 1 ?>">&raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
  <?php endif; ?>

  <?php elseif ($tab === 'categorias'): ?>

  <div class="admin-header">
    <div>
      <h1>Categorias</h1>
      <span>Gerenciar categorias de vagas</span>
    </div>
  </div>

  <?php if ($mensagemCategoria): ?>
    <div class="admin-card" style="margin-bottom:16px;padding:16px 24px;border-left:4px solid #1a7d1a">
      <p style="font-weight:600;margin:0;color:#1a7d1a"><?php echo htmlspecialchars($mensagemCategoria, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <?php if ($erroCategoria): ?>
    <div class="admin-card" style="margin-bottom:16px;padding:16px 24px;border-left:4px solid #ba1a1a">
      <p style="font-weight:600;margin:0;color:#ba1a1a"><?php echo htmlspecialchars($erroCategoria, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php endif; ?>

  <div class="admin-card" style="margin-bottom:24px">
    <h2 style="font-size:18px;font-weight:700;margin:0 0 16px">Nova Categoria</h2>
    <form method="post" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;align-items:end">
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Nome (PT) *</label>
        <input type="text" name="nome_pt" required class="admin-search-input" style="width:100%" placeholder="Ex: Jurídico">
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Nome (EN) *</label>
        <input type="text" name="nome_en" required class="admin-search-input" style="width:100%" placeholder="Ex: Legal">
      </div>
      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Slug *</label>
        <input type="text" name="slug" required class="admin-search-input" style="width:100%" placeholder="Ex: juridico" pattern="[a-z0-9-]+" title="Apenas letras minúsculas, números e hífens">
      </div>
      <div style="grid-column:1/-1">
        <button type="submit" name="criar_categoria" class="btn-search" style="font-size:15px;padding:12px 32px">Criar Categoria</button>
      </div>
    </form>
  </div>

  <?php if (empty($categoriasLista)): ?>
    <div class="admin-empty">Nenhuma categoria encontrada.</div>
  <?php else: ?>
    <div style="overflow-x:auto;background:#fff;border-radius:0.75rem;border:1px solid #c6c6cd">
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
          <tr style="background:#f8f9fa;border-bottom:2px solid #c6c6cd">
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">ID</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Nome (PT)</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Nome (EN)</th>
            <th style="padding:12px 16px;text-align:left;font-weight:700;color:#0b1c30">Slug</th>
            <th style="padding:12px 16px;text-align:center;font-weight:700;color:#0b1c30">Vagas</th>
            <th style="padding:12px 16px;text-align:center;font-weight:700;color:#0b1c30">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categoriasLista as $cat): ?>
            <tr style="border-bottom:1px solid #e5e7eb">
              <td style="padding:10px 16px;font-weight:600;color:#45464d"><?php echo (int)$cat['id'] ?></td>
              <td style="padding:10px 16px;font-weight:600;color:#0b1c30"><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px;color:#45464d"><?php echo htmlspecialchars($cat['nome_en'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px;color:#4b41e1;font-family:monospace"><?php echo htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?></td>
              <td style="padding:10px 16px;text-align:center;font-weight:600"><?php echo (int)$cat['total_vagas'] ?></td>
              <td style="padding:10px 16px;text-align:center">
                <?php if ($cat['slug'] !== 'sem-categoria'): ?>
                  <a href="?tab=categorias&deletar_categoria=<?php echo (int)$cat['id'] ?>" onclick="return confirm('Excluir a categoria &quot;<?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?>&quot;?')" style="font-size:13px;font-weight:600;padding:6px 16px;border-radius:0.5rem;border:1px solid #ba1a1a;color:#ba1a1a;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Excluir</a>
                <?php else: ?>
                  <span style="font-size:12px;color:#76777d">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <?php else: ?>

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

  <details class="cat-filter"<?php echo !empty($catFilter) ? ' open' : '' ?>>
    <summary class="cat-filter-summary">Categorias (tags) <?php echo !empty($catFilter) ? '— ' . count($catFilter) . ' ativo(s)' : '' ?></summary>
    <form class="cat-filter-form" method="get">
      <div class="cat-filter-grid">
        <?php foreach ($todasCategorias as $cat): ?>
          <label class="cat-filter-checkbox">
            <input type="checkbox" name="categorias[]" value="<?php echo $cat['slug'] ?>" <?php echo in_array($cat['slug'], $catFilter) ? 'checked' : '' ?>>
            <span><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></span>
          </label>
        <?php endforeach; ?>
        <label class="cat-filter-checkbox">
          <input type="checkbox" name="categorias[]" value="sem-categoria" <?php echo $semCategoria ? 'checked' : '' ?>>
          <span style="color:#ba1a1a">Sem categoria</span>
        </label>
      </div>
      <div style="display:flex;gap:8px;margin-top:8px">
        <?php if ($origemFilter !== ''): ?>
          <input type="hidden" name="origem" value="<?php echo htmlspecialchars($origemFilter, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <?php if ($statusFilter !== ''): ?>
          <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <?php if ($searchQuery !== ''): ?>
          <input type="hidden" name="q" value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
        <button type="submit" class="btn-search">Filtrar</button>
        <?php if (!empty($catFilter)): ?>
          <a href="admin.php<?php echo $origemParam . $statusParam . $qParam ?>" class="btn-clear">Limpar filtros</a>
        <?php endif; ?>
      </div>
    </form>
  </details>

  <form class="admin-search" method="get">
    <input type="search" name="q" class="admin-search-input" placeholder="Buscar por título, empresa ou local..." value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" autofocus>
    <?php if ($origemFilter !== ''): ?>
      <input type="hidden" name="origem" value="<?php echo htmlspecialchars($origemFilter, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if ($statusFilter !== ''): ?>
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php foreach ($catFilter as $slug): ?>
      <input type="hidden" name="categorias[]" value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <button type="submit" class="btn-search">Buscar</button>
    <?php if ($searchQuery !== '' || !empty($catFilter)): ?>
      <a href="admin.php<?php echo $origemParam . $statusParam ?>" class="btn-clear">Limpar</a>
    <?php endif; ?>
  </form>

  <div class="batch-bar" id="batch-bar-top"></div>

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
          <?php if (!empty($v['tags_str'])): ?>
            <span style="font-size:12px;color:#4b41e1;font-weight:600"><?php echo htmlspecialchars($v['tags_str'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
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
          <a href="admin.php?tab=editar&id=<?php echo (int)$v['id'] . $origemParam . $statusParam . $qParam . $categoriasParam ?>" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #4b41e1;color:#4b41e1;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Editar</a>
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
      <div class="admin-pagination">
        <?php if ($page > 1): ?>
          <a href="?page=<?php echo $page - 1 . $origemParam . $statusParam . $qParam . $categoriasParam ?>">&laquo;</a>
        <?php endif; ?>
        <?php
        $startPage = max(1, $page - 4);
        $endPage = min($totalPages, $page + 4);
        for ($i = $startPage; $i <= $endPage; $i++): ?>
          <?php if ($i === $page): ?>
            <span class="current"><?php echo $i ?></span>
          <?php else: ?>
            <a href="?page=<?php echo $i . $origemParam . $statusParam . $qParam . $categoriasParam ?>"><?php echo $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?php echo $page + 1 . $origemParam . $statusParam . $qParam . $categoriasParam ?>">&raquo;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="batch-bar" id="batch-bar-bottom"></div>

  <?php endif; ?>

  <template id="batch-bar-tpl">
    <label class="batch-select-all"><input type="checkbox" class="select-all"> Selecionar todas</label>
    <span class="batch-count">0 selecionadas</span>
    <button type="button" class="btn-toggle inativar" onclick="batchToggle('inativar')">Inativar Selecionadas</button>
    <button type="button" class="btn-toggle ativar" onclick="batchToggle('ativar')">Ativar Selecionadas</button>
    <button type="button" class="btn-toggle inativar" onclick="batchToggle('remover')">Remover Selecionadas</button>
    <span style="border-left:1px solid #ccc;height:24px;margin:0 8px"></span>
    <select id="batch-cat-select" multiple style="max-width:220px;font-size:12px;padding:4px;border:1px solid #ccc;border-radius:4px;min-height:28px">
      <?php foreach ($todasCategorias as $cat): ?>
        <?php if ($cat['slug'] !== 'sem-categoria'): ?>
          <option value="<?php echo $cat['slug'] ?>"><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn-toggle ativar" onclick="batchCategorize()" style="padding:7px 14px;font-size:12px">Categorizar</button>
    <button type="button" class="btn-toggle inativar" onclick="batchRemoveCats()" style="padding:7px 14px;font-size:12px">Remover Categorias</button>
  </template>

  <form id="batch-form" method="post" style="display:none">
    <input type="hidden" name="batch_ids" id="batch-ids">
    <input type="hidden" name="batch_action" id="batch-action">
    <input type="hidden" name="batch_categorize" id="batch-cats">
    <input type="hidden" name="batch_remove_cats" id="batch-remove-cats">
  </form>
</main>

<script>
(function() {
  var manterData = document.getElementById('manter_data');
  var campoData = document.getElementById('campo_data');
  if (manterData && campoData) {
    function toggleDataField() {
      campoData.style.display = manterData.checked ? 'none' : 'block';
    }
    manterData.addEventListener('change', toggleDataField);
    toggleDataField();
  }

  var tpl = document.getElementById('batch-bar-tpl');
  var topBar = document.getElementById('batch-bar-top');
  var bottomBar = document.getElementById('batch-bar-bottom');
  topBar.appendChild(tpl.content.cloneNode(true));
  bottomBar.appendChild(tpl.content.cloneNode(true));

  var checks = document.querySelectorAll('.batch-check');
  var countEls = document.querySelectorAll('.batch-count');
  var selectAlls = document.querySelectorAll('.select-all');

  function updateCount() {
    var checked = document.querySelectorAll('.batch-check:checked').length;
    countEls.forEach(function(el) { el.textContent = checked + ' selecionada(s)'; });
    selectAlls.forEach(function(el) { el.checked = checked === checks.length; });
  }

  selectAlls.forEach(function(sa) {
    sa.addEventListener('change', function() {
      checks.forEach(function(cb) { cb.checked = sa.checked; });
      updateCount();
    });
  });

  checks.forEach(function(cb) {
    cb.addEventListener('change', updateCount);
  });

  window.batchToggle = function(action) {
    var ids = [];
    document.querySelectorAll('.batch-check:checked').forEach(function(cb) {
      ids.push(cb.value);
    });
    if (!ids.length) return;
    var msgs = { inativar: 'Inativar', ativar: 'Ativar', remover: 'Remover da lista de novas' };
    var msg = msgs[action] || action;
    if (!confirm(msg + ' ' + ids.length + ' vaga(s)?')) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = action;
    document.getElementById('batch-cats').value = '';
    document.getElementById('batch-remove-cats').value = '';
    document.getElementById('batch-form').submit();
  };

  window.batchCategorize = function() {
    var ids = [];
    document.querySelectorAll('.batch-check:checked').forEach(function(cb) {
      ids.push(cb.value);
    });
    if (!ids.length) return alert('Selecione ao menos uma vaga.');
    var sel = document.getElementById('batch-cat-select');
    var cats = [];
    sel.querySelectorAll('option:checked').forEach(function(opt) { cats.push(opt.value); });
    if (!cats.length) return alert('Selecione ao menos uma categoria.');
    if (!confirm('Categorizar ' + ids.length + ' vaga(s) com ' + cats.length + ' categoria(s)?')) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = '';
    document.getElementById('batch-cats').value = cats.join(',');
    document.getElementById('batch-remove-cats').value = '';
    document.getElementById('batch-form').submit();
  };

  window.batchRemoveCats = function() {
    var ids = [];
    document.querySelectorAll('.batch-check:checked').forEach(function(cb) {
      ids.push(cb.value);
    });
    if (!ids.length) return alert('Selecione ao menos uma vaga.');
    var sel = document.getElementById('batch-cat-select');
    var cats = [];
    sel.querySelectorAll('option:checked').forEach(function(opt) { cats.push(opt.value); });
    if (!cats.length) return alert('Selecione ao menos uma categoria para remover.');
    if (!confirm('Remover ' + cats.length + ' categoria(s) de ' + ids.length + ' vaga(s)?')) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = '';
    document.getElementById('batch-cats').value = '';
    document.getElementById('batch-remove-cats').value = cats.join(',');
    document.getElementById('batch-form').submit();
  };
})();
</script>

<?php endif; ?>
</body>
</html>
