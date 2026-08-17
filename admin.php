<?php
session_start();
require_once __DIR__ . '/App/Autoloader.php';

$configFile = file_exists(__DIR__ . '/config.local.php') ? __DIR__ . '/config.local.php' : __DIR__ . '/config.php';
$config = require $configFile;
$adminPassword = $config['admin_password'] ?? '';

require_once __DIR__ . '/categorias.php';
require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/VagaRepository.php';

function agendarTempoRestante($dtStr) {
    if (!$dtStr) return '';
    $timestamp = strtotime($dtStr);
    $diff = $timestamp - time();
    if ($diff <= 0) return 'Processando...';
    $dias = floor($diff / 86400);
    $horas = floor(($diff % 86400) / 3600);
    $minutos = floor(($diff % 3600) / 60);
    $partes = [];
    if ($dias > 0) $partes[] = "{$dias}d";
    if ($horas > 0) $partes[] = "{$horas}h";
    if ($minutos > 0 || empty($partes)) $partes[] = "{$minutos}min";
    return 'em ' . implode(' ', $partes);
}

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

$origemFilter = isset($_GET['origem']) && in_array($_GET['origem'], ['nacional', 'exterior']) ? $_GET['origem'] : '';
$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['ativa', 'inativa']) ? $_GET['status'] : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

$qParam = $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '';
$origemParam = $origemFilter !== '' ? '&origem=' . urlencode($origemFilter) : '';
$statusParam = $statusFilter !== '' ? '&status=' . urlencode($statusFilter) : '';


if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_ids']) && !empty($_POST['batch_action'])) {
    try {
        $pdo = conectarBanco($config);
        $ids = array_map('intval', explode(',', $_POST['batch_ids']));
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            if ($_POST['batch_action'] === 'remover') {
                $stmt = $pdo->prepare("UPDATE vagas SET revisada_em = NOW() WHERE id IN ($placeholders) AND revisada_em IS NULL");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'tornar_premium') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_premium = 1, destaque_ate = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'remover_premium') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_premium = 0, destaque_ate = NULL WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'favoritar') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_favorita = 1 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'desfavoritar') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_favorita = 0 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'nao_listada') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_nao_listada = 1 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'listada') {
                $stmt = $pdo->prepare("UPDATE vagas SET is_nao_listada = 0 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'cancelar_agendamento') {
                $stmt = $pdo->prepare("UPDATE vagas SET agendado_ativar_em = NULL, agendado_desativar_em = NULL WHERE id IN ($placeholders)");
                $stmt->execute($ids);
            } elseif ($_POST['batch_action'] === 'agendar_ativar' && (!empty($_POST['batch_schedule_datetime']) || !empty($_POST['batch_offset_minutes']))) {
                if (!empty($_POST['batch_schedule_datetime'])) {
                    $ts = strtotime($_POST['batch_schedule_datetime']);
                    if ($ts) {
                        $dtStr = date('Y-m-d H:i:s', $ts);
                        $stmt = $pdo->prepare("UPDATE vagas SET status = 'inativa', agendado_ativar_em = ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$dtStr], $ids));
                    }
                } else {
                    $mins = max(1, (int)$_POST['batch_offset_minutes']);
                    $stmt = $pdo->prepare("UPDATE vagas SET status = 'inativa', agendado_ativar_em = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$mins], $ids));
                }
            } elseif ($_POST['batch_action'] === 'agendar_desativar' && (!empty($_POST['batch_schedule_datetime']) || !empty($_POST['batch_offset_minutes']))) {
                if (!empty($_POST['batch_schedule_datetime'])) {
                    $ts = strtotime($_POST['batch_schedule_datetime']);
                    if ($ts) {
                        $dtStr = date('Y-m-d H:i:s', $ts);
                        $stmt = $pdo->prepare("UPDATE vagas SET agendado_desativar_em = ? WHERE id IN ($placeholders)");
                        $stmt->execute(array_merge([$dtStr], $ids));
                    }
                } else {
                    $mins = max(1, (int)$_POST['batch_offset_minutes']);
                    $stmt = $pdo->prepare("UPDATE vagas SET agendado_desativar_em = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id IN ($placeholders)");
                    $stmt->execute(array_merge([$mins], $ids));
                }
            } else {
                $targetStatus = $_POST['batch_action'] === 'ativar' ? 'ativa' : 'inativa';
                $stmt = $pdo->prepare("UPDATE vagas SET status = ?, publicado_em = IF(? = 'ativa', NOW(), publicado_em), agendado_ativar_em = NULL, agendado_desativar_em = NULL WHERE id IN ($placeholders)");
                $stmt->execute(array_merge([$targetStatus, $targetStatus], $ids));
            }
        }
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_nao_listada_id'])) {
    try {
        $pdo = conectarBanco($config);
        $stmt = $pdo->prepare("UPDATE vagas SET is_nao_listada = IF(is_nao_listada = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => (int)$_POST['toggle_nao_listada_id']]);
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
        $stmt = $pdo->prepare("UPDATE vagas SET status = IF(status = 'ativa', 'inativa', 'ativa'), publicado_em = IF(status = 'inativa', NOW(), publicado_em), agendado_ativar_em = NULL, agendado_desativar_em = NULL WHERE id = :id");
        $stmt->execute([':id' => (int)$_POST['toggle_id']]);
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorita_id'])) {
    try {
        $pdo = conectarBanco($config);
        $stmt = $pdo->prepare("UPDATE vagas SET is_favorita = IF(is_favorita = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => (int)$_POST['toggle_favorita_id']]);
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['executar_agendamento_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $vId = (int)$_POST['executar_agendamento_id'];
        $stmt = $pdo->prepare("UPDATE vagas SET publicado_em = IF(agendado_ativar_em IS NOT NULL, NOW(), publicado_em), status = CASE WHEN agendado_ativar_em IS NOT NULL THEN 'ativa' WHEN agendado_desativar_em IS NOT NULL THEN 'inativa' ELSE status END, agendado_ativar_em = NULL, agendado_desativar_em = NULL WHERE id = :id");
        $stmt->execute([':id' => $vId]);
    } catch (Exception $e) {}
    header('Location: admin.php?page=' . ((int)($_GET['page'] ?? 1)) . $redirectTab . $redirectNs . $redirectMostrar . $origemParam . $statusParam . $qParam);
    exit;
}

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_agendamento_id'])) {
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $vId = (int)$_POST['cancelar_agendamento_id'];
        $stmt = $pdo->prepare("UPDATE vagas SET agendado_ativar_em = NULL, agendado_desativar_em = NULL WHERE id = :id");
        $stmt->execute([':id' => $vId]);
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

        $agendadoAtivarVal = null;
        $ativarOffset = isset($_POST['agendado_ativar_offset']) && $_POST['agendado_ativar_offset'] !== '' ? (int)$_POST['agendado_ativar_offset'] : null;
        if ($ativarOffset !== null && $ativarOffset > 0) {
            $agendadoAtivarVal = date('Y-m-d H:i:s', time() + ($ativarOffset * 60));
        } elseif (!empty($_POST['agendado_ativar_em'])) {
            $ts = strtotime($_POST['agendado_ativar_em']);
            if ($ts) $agendadoAtivarVal = date('Y-m-d H:i:s', $ts);
        }

        $agendadoDesativarVal = null;
        $desativarOffset = isset($_POST['agendado_desativar_offset']) && $_POST['agendado_desativar_offset'] !== '' ? (int)$_POST['agendado_desativar_offset'] : null;
        if ($desativarOffset !== null && $desativarOffset > 0) {
            $agendadoDesativarVal = date('Y-m-d H:i:s', time() + ($desativarOffset * 60));
        } elseif (!empty($_POST['agendado_desativar_em'])) {
            $ts = strtotime($_POST['agendado_desativar_em']);
            if ($ts) $agendadoDesativarVal = date('Y-m-d H:i:s', $ts);
        }

        $stmt = $pdo->prepare("INSERT INTO vagas (vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, descricao, resumo, publicado_em, status, origem, area, agendado_ativar_em, agendado_desativar_em) VALUES (:id, :titulo, :empresa, :local, :modelo, :url, :desc, :resumo, :publicado, 'inativa', :origem, :area, :agendado_ativar, :agendado_desativar)");
        $stmt->execute([
            ':id'                 => $vagaId,
            ':titulo'             => $titulo,
            ':empresa'            => $empresa,
            ':local'              => $localizacao ?: null,
            ':modelo'             => $modeloTrabalho ?: null,
            ':url'                => $urlVaga ?: null,
            ':desc'               => $descricao,
            ':resumo'             => $resumo,
            ':publicado'          => $publicadoEm ?: null,
            ':origem'             => $origem,
            ':area'               => $primeiraArea,
            ':agendado_ativar'    => $agendadoAtivarVal,
            ':agendado_desativar' => $agendadoDesativarVal,
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

        $isNaoListada = !empty($_POST['is_nao_listada']) ? 1 : 0;

        $setParts = ['titulo = :titulo', 'empresa = :empresa', 'localizacao = :local', 'modelo_trabalho = :modelo', 'url_vaga = :url', 'descricao = :desc', 'resumo = :resumo', 'origem = :origem', 'area = :area', 'status = :status', 'is_nao_listada = :is_nao_listada'];
        $params = [
            ':id'             => $id,
            ':titulo'         => $titulo,
            ':empresa'        => $empresa,
            ':local'          => $localizacao ?: null,
            ':modelo'         => $modeloTrabalho ?: null,
            ':url'            => $urlVaga ?: null,
            ':desc'           => $descricao,
            ':resumo'         => $resumo,
            ':origem'         => $origem,
            ':area'           => $primeiraArea,
            ':status'         => $status,
            ':is_nao_listada' => $isNaoListada,
        ];

        if (isset($_POST['is_premium'])) {
            $setParts[] = 'is_premium = :is_premium';
            $params[':is_premium'] = (int)$_POST['is_premium'];
        }


        if (!isset($_POST['manter_data'])) {
            $publicadoEm = trim($_POST['publicado_em'] ?? '') ?: date('Y-m-d H:i:s');
            $setParts[] = 'publicado_em = :publicado';
            $params[':publicado'] = $publicadoEm;
        }

        if (isset($_POST['limpar_agendamento_ativar'])) {
            $setParts[] = 'agendado_ativar_em = NULL';
        } else {
            $ativarOffset = isset($_POST['agendado_ativar_offset']) && $_POST['agendado_ativar_offset'] !== '' ? (int)$_POST['agendado_ativar_offset'] : null;
            if ($ativarOffset !== null && $ativarOffset > 0) {
                $setParts[] = "agendado_ativar_em = DATE_ADD(NOW(), INTERVAL {$ativarOffset} MINUTE)";
                $params[':status'] = 'inativa';
            } elseif (!empty($_POST['agendado_ativar_em'])) {
                $ts = strtotime($_POST['agendado_ativar_em']);
                if ($ts) {
                    $setParts[] = 'agendado_ativar_em = ' . $pdo->quote(date('Y-m-d H:i:s', $ts));
                    $params[':status'] = 'inativa';
                }
            }
        }

        if (isset($_POST['limpar_agendamento_desativar'])) {
            $setParts[] = 'agendado_desativar_em = NULL';
        } else {
            $desativarOffset = isset($_POST['agendado_desativar_offset']) && $_POST['agendado_desativar_offset'] !== '' ? (int)$_POST['agendado_desativar_offset'] : null;
            if ($desativarOffset !== null && $desativarOffset > 0) {
                $setParts[] = "agendado_desativar_em = DATE_ADD(NOW(), INTERVAL {$desativarOffset} MINUTE)";
            } elseif (!empty($_POST['agendado_desativar_em'])) {
                $ts = strtotime($_POST['agendado_desativar_em']);
                if ($ts) {
                    $setParts[] = 'agendado_desativar_em = ' . $pdo->quote(date('Y-m-d H:i:s', $ts));
                }
            }
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

$tab = isset($_GET['tab']) && in_array($_GET['tab'], ['cadastro', 'editar', 'emails', 'blog', 'novas', 'categorias', 'por-categorias', 'agendadas', 'favoritas']) ? $_GET['tab'] : 'lista';

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

$catFilter = isset($_GET['categorias']) && is_array($_GET['categorias']) ? $_GET['categorias'] : [];
$semCategoria = in_array('sem-categoria', $catFilter);
$catSlugs = array_values(array_filter($catFilter, fn($s) => $s !== 'sem-categoria'));

$tabParam = $tab !== 'lista' ? '&tab=' . $tab : '';
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
<link rel="stylesheet" href="/css/style.css?v=2.4.0">
<style>
.admin-nav { background: #0b1c30; height: 64px; }
.admin-nav .nav-inner { height: 64px; }
.admin-nav .nav-logo { color: #fff; font-size: 1.25rem; font-weight: 700; }
.admin-link { font-size: 14px; font-weight: 500; color: #c6c6cd; transition: color 0.2s; text-decoration: none; }
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
.admin-main { max-width: 1320px; margin: 80px auto 48px; padding: 0 16px; }
@media (min-width: 768px) { .admin-main { padding: 0 40px; } }
.admin-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
.admin-header h1 { font-size: 1.65rem; font-weight: 700; color: #0b1c30; letter-spacing: -0.02em; margin: 0; }
.admin-header span { color: #64748b; font-size: 14px; font-weight: 500; }
.admin-stats { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
.stat { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.6rem; padding: 10px 18px; font-size: 13px; font-weight: 600; color: #475569; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
.stat strong { color: #4b41e1; font-size: 15px; }

/* Modern Top Header Navigation */
.admin-nav-bar { margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; }
.admin-tabs { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: -2px; }
.admin-tab { 
  display: inline-flex; align-items: center; gap: 8px; padding: 11px 18px; 
  font-size: 14px; font-weight: 600; color: #475569; border: 1px solid transparent; 
  border-bottom: 2px solid transparent; border-radius: 0.6rem 0.6rem 0 0; 
  transition: all 0.2s ease; text-decoration: none; background: transparent; 
}
.admin-tab:hover { color: #4b41e1; background: #f8fafc; }
.admin-tab.active { color: #4b41e1; background: #fff; border-color: #e2e8f0 #e2e8f0 #fff; border-bottom: 2px solid #fff; box-shadow: 0 -2px 6px rgba(0,0,0,0.03); }

/* Badges inside navigation tabs */
.nav-badge {
  font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 999px;
  background: #e2e8f0; color: #334155; display: inline-flex; align-items: center; justify-content: center;
}
.admin-tab.active .nav-badge { background: #e0e7ff; color: #4338ca; }
.nav-badge-favorita { background: #fef3c7; color: #b45309; }
.nav-badge-novas { background: #fee2e2; color: #b91c1c; }
.nav-badge-agendadas { background: #e0e7ff; color: #4338ca; }

/* Sub-filter Secondary Bar (Status & Origem) */
.admin-subfilter-bar {
  display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 12px 18px; margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.filter-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.filter-label { font-size: 12px; font-weight: 700; color: #64748b; margin-right: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
.filter-btn {
  font-size: 13px; font-weight: 600; color: #475569; padding: 6px 14px; border-radius: 0.5rem;
  border: 1px solid #e2e8f0; background: #f8fafc; text-decoration: none; transition: all 0.2s ease;
  display: inline-flex; align-items: center; gap: 4px;
}
.filter-btn:hover { border-color: #cbd5e1; color: #0f172a; background: #fff; }
.filter-btn.active { background: #4b41e1; color: #fff; border-color: #4b41e1; box-shadow: 0 2px 4px rgba(75, 65, 225, 0.25); }

/* Favorite Button & Badges */
.btn-favorite {
  font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 0.5rem;
  border: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;
}
.btn-favorite:hover { border-color: #facc15; background: #fffbe6; color: #d97706; transform: translateY(-1px); }
.btn-favorite.active { background: #fef3c7; border-color: #f59e0b; color: #b45309; font-weight: 700; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2); }
.badge-favorita { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }

/* Job Cards & Actions */
.admin-card { background: #fff; border-radius: 0.75rem; padding: 22px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; transition: all 0.25s ease; }
.admin-card:hover { box-shadow: 0 8px 16px -2px rgba(0,0,0,0.08); border-color: #cbd5e1; }
.admin-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.admin-card-title { font-size: 18px; line-height: 24px; font-weight: 700; color: #0b1c30; word-break: break-word; }
.admin-card-company { font-size: 13px; line-height: 18px; font-weight: 500; color: #64748b; margin-top: 3px; word-break: break-word; }
.admin-card-meta { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 12px; }
.admin-card-meta span { font-size: 12px; line-height: 16px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 4px; }
.badge-origem { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-origem.exterior { background: #fff3e5; color: #b55a00; border-color: #ffe0b3; }
.badge-status { border-radius: 9999px; padding: 3px 10px; font-size: 11px; font-weight: 600; }
.badge-status.ativa { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-status.inativa { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

/* Enhanced Action Buttons Container */
.admin-card-actions { margin-top: 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 12px; }
.btn-toggle { font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 0.5rem; border: 1px solid; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px; }
.btn-toggle.inativar { color: #b91c1c; border-color: #fca5a5; background: #fff; }
.btn-toggle.inativar:hover { background: #fee2e2; }
.btn-toggle.ativar { color: #15803d; border-color: #bbf7d0; background: #fff; }
.btn-toggle.ativar:hover { background: #dcfce7; }

.btn-action-edit {
  font-size: 13px; font-weight: 600; padding: 7px 18px; border-radius: 0.5rem;
  border: 1px solid #4b41e1; color: #4b41e1; background: #f8faff; text-decoration: none;
  display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease;
}
.btn-action-edit:hover { background: #4b41e1; color: #fff; }
.btn-action-view {
  font-size: 13px; font-weight: 500; color: #334155; padding: 7px 18px;
  border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #fff; text-decoration: none;
  display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease;
}
.btn-action-view:hover { border-color: #4b41e1; color: #4b41e1; background: #f8fafc; }
.btn-action-copy {
  font-size: 12px; font-weight: 500; padding: 7px 14px; background: #fff;
  border: 1px solid #cbd5e1; border-radius: 0.5rem; cursor: pointer; color: #475569;
  display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease;
}
.btn-action-copy:hover { border-color: #94a3b8; color: #0f172a; background: #f8fafc; }
.btn-action-export {
  font-size: 12px; font-weight: 600; padding: 7px 14px; background: #f5f3ff;
  border: 1px solid #ddd6fe; border-radius: 0.5rem; cursor: pointer; color: #6b21a8;
  display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s ease;
}
.btn-action-export:hover { background: #ede9fe; border-color: #c4b5fd; color: #581c87; }
.btn-action-export.copied { background: #dcfce7 !important; border-color: #86efac !important; color: #166534 !important; }

.admin-pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 32px; flex-wrap: wrap; }
.admin-pagination a, .admin-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 12px; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 14px; font-weight: 500; color: #0b1c30; background: #fff; transition: all 0.2s; text-decoration: none; }
.admin-pagination a:hover { border-color: #4b41e1; color: #4b41e1; }
.admin-pagination .current { background: #4b41e1; color: #fff; border-color: #4b41e1; }
.admin-empty { text-align: center; padding: 64px 16px; color: #64748b; font-size: 15px; }

.batch-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; padding: 12px 16px; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
.batch-select-all { font-size: 14px; font-weight: 600; color: #0b1c30; cursor: pointer; display: flex; align-items: center; gap: 6px; }
.batch-select-all input { width: 16px; height: 16px; cursor: pointer; }
.batch-count { font-size: 13px; color: #64748b; margin-right: auto; }
.admin-check-wrap { display: flex; align-items: center; padding: 4px 8px 0 0; }
.admin-check-wrap input { width: 18px; height: 18px; cursor: pointer; }
.admin-search { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
.admin-search-input { flex: 1; min-width: 200px; background: #fff; border: 1px solid #cbd5e1; border-radius: 0.5rem; padding: 10px 16px; font-size: 14px; color: #0b1c30; outline: none; transition: border-color 0.2s; }
.admin-search-input:focus { border-color: #4b41e1; box-shadow: 0 0 0 1px #4b41e1; }
.btn-search { background: #4b41e1; color: #fff; font-size: 13px; font-weight: 600; padding: 10px 20px; border: none; border-radius: 0.5rem; cursor: pointer; transition: background 0.3s; }
.btn-search:hover { background: #645efb; }
.btn-clear { display: inline-flex; align-items: center; font-size: 13px; font-weight: 500; color: #64748b; padding: 10px 16px; border: 1px solid #cbd5e1; border-radius: 0.5rem; text-decoration: none; transition: all 0.2s; }
.btn-clear:hover { border-color: #b91c1c; color: #b91c1c; }
.cat-checkboxes { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
.cat-checkbox { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #0b1c30; cursor: pointer; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #fff; transition: all 0.2s; }
.cat-checkbox:hover { border-color: #4b41e1; }
.cat-checkbox input { width: 16px; height: 16px; cursor: pointer; }
.cat-filter { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-bottom: 16px; overflow: hidden; }
.cat-filter-summary { padding: 10px 16px; font-size: 14px; font-weight: 600; cursor: pointer; color: #0b1c30; user-select: none; }
.cat-filter-summary:hover { background: #f8fafc; }
.cat-filter-form { padding: 0 16px 16px; border-top: 1px solid #e2e8f0; padding-top: 12px; }
.cat-filter-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.cat-filter-checkbox { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: #0b1c30; cursor: pointer; padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 0.5rem; background: #f8fafc; transition: all 0.2s; }
.cat-filter-checkbox:hover { border-color: #4b41e1; }
.cat-filter-checkbox input { width: 16px; height: 16px; cursor: pointer; }
.cat-filter-checkbox input { width: 16px; height: 16px; cursor: pointer; }
.cat-grid-wrapper { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 12px; margin-bottom: 24px; }
.cat-tile { background: #fff; border: 1px solid #c6c6cd; border-radius: 0.75rem; padding: 14px 18px; text-decoration: none; color: #0b1c30; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease-in-out; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.cat-tile:hover { border-color: #4b41e1; box-shadow: 0 4px 12px rgba(75, 65, 225, 0.15); transform: translateY(-2px); }
.cat-tile.active { background: #4b41e1; border-color: #4b41e1; color: #fff; box-shadow: 0 4px 12px rgba(75, 65, 225, 0.3); }
.cat-tile-name { font-size: 15px; font-weight: 700; line-height: 1.3; }
.cat-tile.active .cat-tile-name { color: #fff; }
.cat-tile-count { font-size: 12px; font-weight: 600; color: #64656c; margin-top: 10px; }
.cat-tile.active .cat-tile-count { color: #e0e0ff; }
.cat-tile-sem-cat { border-color: #f5baba; background: #fffafa; }
.cat-tile-sem-cat .cat-tile-name { color: #ba1a1a; }
.cat-tile-sem-cat.active { background: #ba1a1a; border-color: #ba1a1a; }
.cat-tile-sem-cat.active .cat-tile-name, .cat-tile-sem-cat.active .cat-tile-count { color: #fff; }
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
    $pdo = conectarBanco($config);

    setupSchema($pdo);
    processarAgendamentosVagas($pdo);

    $whereClauses = [];
    if ($tab === 'favoritas' || $statusFilter === 'favorita') {
        $whereClauses[] = "vagas.is_favorita = 1";
    }
    if ($origemFilter !== '') $whereClauses[] = "vagas.origem = " . $pdo->quote($origemFilter);
    if ($statusFilter !== '' && $statusFilter !== 'favorita') $whereClauses[] = "vagas.status = " . $pdo->quote($statusFilter);
    if ($searchQuery !== '') {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $searchQuery);
        $like = $pdo->quote('%' . $escaped . '%');
        if (is_numeric($searchQuery)) {
            $idVal = (int)$searchQuery;
            $whereClauses[] = "(vagas.id = $idVal OR vagas.vaga_id_externo LIKE $like OR vagas.titulo LIKE $like OR vagas.empresa LIKE $like OR vagas.localizacao LIKE $like)";
        } else {
            $whereClauses[] = "(vagas.id LIKE $like OR vagas.vaga_id_externo LIKE $like OR vagas.titulo LIKE $like OR vagas.empresa LIKE $like OR vagas.localizacao LIKE $like)";
        }
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

    $stmt = $pdo->prepare("SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, vagas.modelo_trabalho, vagas.descricao, vagas.resumo, vagas.status, vagas.origem, vagas.area, vagas.publicado_em, vagas.agendado_ativar_em, vagas.agendado_desativar_em, vagas.is_premium, vagas.is_favorita, vagas.is_nao_listada, DATE_FORMAT(vagas.publicado_em, '%d/%m/%Y') as publicado_em_fmt, GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str FROM vagas LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id LEFT JOIN categorias c ON c.id = vc.categoria_id" . $where . " GROUP BY vagas.id ORDER BY vagas.publicado_em DESC, vagas.data_coleta DESC LIMIT :limit OFFSET :offset");

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vagas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalAgendadasCount = (int)$pdo->query("SELECT COUNT(*) FROM vagas WHERE agendado_ativar_em IS NOT NULL OR agendado_desativar_em IS NOT NULL")->fetchColumn();
    $totalFavoritasCount = (int)$pdo->query("SELECT COUNT(*) FROM vagas WHERE is_favorita = 1")->fetchColumn();
    $totalNovasCount = (int)$pdo->query("SELECT COUNT(*) FROM vagas WHERE created_at >= NOW() - INTERVAL 24 HOUR AND revisada_em IS NULL")->fetchColumn();
    $vagasAgendadas = [];
    if ($tab === 'agendadas') {
        $stmtAg = $pdo->query("
            SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, vagas.modelo_trabalho, vagas.resumo,
                   vagas.status, vagas.origem, vagas.publicado_em, vagas.agendado_ativar_em, vagas.agendado_desativar_em,
                   DATE_FORMAT(vagas.agendado_ativar_em, '%d/%m/%Y %H:%i') as agendado_ativar_fmt,
                   DATE_FORMAT(vagas.agendado_desativar_em, '%d/%m/%Y %H:%i') as agendado_desativar_fmt,
                   GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str
            FROM vagas
            LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id
            LEFT JOIN categorias c ON c.id = vc.categoria_id
            WHERE vagas.agendado_ativar_em IS NOT NULL OR vagas.agendado_desativar_em IS NOT NULL
            GROUP BY vagas.id
            ORDER BY COALESCE(vagas.agendado_ativar_em, vagas.agendado_desativar_em) ASC
        ");
        $vagasAgendadas = $stmtAg ? $stmtAg->fetchAll(PDO::FETCH_ASSOC) : [];
    }

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

    $catSelected = isset($_GET['cat']) ? trim($_GET['cat']) : '';
    $categoriasAtivasList = [];
    $totalSemCategoriaAtivas = 0;
    $vagasPorCat = [];
    $totalVagasCat = 0;
    $totalPagesCat = 1;
    $pageCat = 1;
    $catNomeAtual = '';

    if ($tab === 'por-categorias') {
        $stmtCatsAtivas = $pdo->query("
            SELECT c.*, 
              (SELECT COUNT(DISTINCT v.id) 
               FROM vaga_categorias vc 
               JOIN vagas v ON v.id = vc.vaga_id 
               WHERE vc.categoria_id = c.id AND v.status = 'ativa') AS total_ativas
            FROM categorias c 
            ORDER BY c.nome_pt ASC
        ");
        $categoriasAtivasList = $stmtCatsAtivas->fetchAll(PDO::FETCH_ASSOC);

        $totalSemCategoriaAtivas = (int)$pdo->query("
            SELECT COUNT(*) 
            FROM vagas v 
            WHERE v.status = 'ativa' 
              AND NOT EXISTS (SELECT 1 FROM vaga_categorias vc WHERE vc.vaga_id = v.id)
        ")->fetchColumn();

        if ($catSelected !== '') {
            $whereClausesCat = ["vagas.status = 'ativa'"];

            if ($catSelected === 'sem-categoria') {
                $whereClausesCat[] = "NOT EXISTS (SELECT 1 FROM vaga_categorias vc WHERE vc.vaga_id = vagas.id)";
                $catNomeAtual = 'Sem Categoria';
            } else {
                $whereClausesCat[] = "EXISTS (SELECT 1 FROM vaga_categorias vc JOIN categorias c ON c.id = vc.categoria_id WHERE vc.vaga_id = vagas.id AND c.slug = " . $pdo->quote($catSelected) . ")";
                foreach ($categoriasAtivasList as $ca) {
                    if ($ca['slug'] === $catSelected) {
                        $catNomeAtual = $ca['nome_pt'];
                        break;
                    }
                }
            }

            if ($searchQuery !== '') {
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $searchQuery);
                $like = $pdo->quote('%' . $escaped . '%');
                if (is_numeric($searchQuery)) {
                    $idVal = (int)$searchQuery;
                    $whereClausesCat[] = "(vagas.id = $idVal OR vagas.vaga_id_externo LIKE $like OR vagas.titulo LIKE $like OR vagas.empresa LIKE $like OR vagas.localizacao LIKE $like)";
                } else {
                    $whereClausesCat[] = "(vagas.id LIKE $like OR vagas.vaga_id_externo LIKE $like OR vagas.titulo LIKE $like OR vagas.empresa LIKE $like OR vagas.localizacao LIKE $like)";
                }
            }

            $whereCatSql = " WHERE " . implode(" AND ", $whereClausesCat);

            $pageCat = max(1, (int)($_GET['page'] ?? 1));
            $limitCat = 30;
            $offsetCat = ($pageCat - 1) * $limitCat;

            $totalVagasCat = (int)$pdo->query("SELECT COUNT(DISTINCT vagas.id) FROM vagas " . $whereCatSql)->fetchColumn();
            $totalPagesCat = $totalVagasCat > 0 ? (int)ceil($totalVagasCat / $limitCat) : 1;

            $stmtVagasCat = $pdo->prepare("
                SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, 
                       vagas.modelo_trabalho, vagas.descricao, vagas.resumo, vagas.status, vagas.origem, 
                       vagas.area, vagas.publicado_em, DATE_FORMAT(vagas.publicado_em, '%d/%m/%Y') as publicado_em_fmt, 
                       GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str 
                FROM vagas 
                LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id 
                LEFT JOIN categorias c ON c.id = vc.categoria_id 
                {$whereCatSql} 
                GROUP BY vagas.id 
                ORDER BY vagas.publicado_em DESC, vagas.id DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmtVagasCat->bindValue(':limit', $limitCat, PDO::PARAM_INT);
            $stmtVagasCat->bindValue(':offset', $offsetCat, PDO::PARAM_INT);
            $stmtVagasCat->execute();
            $vagasPorCat = $stmtVagasCat->fetchAll(PDO::FETCH_ASSOC);
        }
    }

} catch (Exception $e) {
    echo '<div class="admin-main"><p style="color:#ba1a1a;">Erro ao conectar ao banco de dados.</p></div>';
    exit;
}
?>

<main class="admin-main">
  <div class="admin-nav-bar">
    <div class="admin-tabs">
      <a class="admin-tab <?php echo ($tab === 'lista' && $statusFilter !== 'favorita') ? 'active' : '' ?>" href="admin.php">
        📋 Vagas
      </a>
      <a class="admin-tab <?php echo ($tab === 'favoritas' || $statusFilter === 'favorita') ? 'active' : '' ?>" href="admin.php?tab=favoritas">
        ⭐ Favoritas <?php echo $totalFavoritasCount > 0 ? "<span class=\"nav-badge nav-badge-favorita\">$totalFavoritasCount</span>" : '' ?>
      </a>
      <a class="admin-tab <?php echo $tab === 'novas' ? 'active' : '' ?>" href="admin.php?tab=novas">
        🔥 Novas (24h) <?php echo $totalNovasCount > 0 ? "<span class=\"nav-badge nav-badge-novas\">$totalNovasCount</span>" : '' ?>
      </a>
      <a class="admin-tab <?php echo $tab === 'agendadas' ? 'active' : '' ?>" href="admin.php?tab=agendadas">
        ⏰ Agendadas <?php echo $totalAgendadasCount > 0 ? "<span class=\"nav-badge nav-badge-agendadas\">$totalAgendadasCount</span>" : '' ?>
      </a>
      <a class="admin-tab <?php echo $tab === 'cadastro' ? 'active' : '' ?>" href="admin.php?tab=cadastro">
        ➕ Cadastrar Vaga
      </a>
      <a class="admin-tab <?php echo $tab === 'categorias' ? 'active' : '' ?>" href="admin.php?tab=categorias">
        📁 Categorias
      </a>
      <a class="admin-tab <?php echo $tab === 'por-categorias' ? 'active' : '' ?>" href="admin.php?tab=por-categorias">
        🔍 Busca por Categorias
      </a>
      <a class="admin-tab <?php echo $tab === 'blog' ? 'active' : '' ?>" href="admin.php?tab=blog">
        📝 Blog
      </a>
      <a class="admin-tab <?php echo $tab === 'emails' ? 'active' : '' ?>" href="admin.php?tab=emails">
        📧 Emails
      </a>
    </div>
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
        <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #cbd5e1;margin-top:8px">
          <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 8px 0;display:flex;align-items:center;gap:6px">
            <span>⏱️</span> Agendamento de Publicação (Relativo ao servidor)
          </h3>
          <p style="font-size:12px;color:#64748b;margin:0 0 12px 0">
            Agende quando esta vaga deve entrar ou sair do ar. Calculado em relação ao relógio do servidor (sem conflito de fuso horário).
          </p>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div style="background:#ffffff;padding:12px;border-radius:6px;border:1px solid #e2e8f0">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#1e293b">🚀 Agendar Ativação</label>
              <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px">
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 30)">+30 min</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 60)">+1h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 120)">+2h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 360)">+6h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 720)">+12h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 1440)">+1 dia</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_ativar', 2880)">+2 dias</button>
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <input type="datetime-local" id="input_cad_ativar_dt" name="agendado_ativar_em" class="admin-search-input" style="flex:1;font-size:13px;padding:6px 10px" onchange="syncDatetimeOffset('cad_ativar')">
                <input type="hidden" id="input_cad_ativar_offset" name="agendado_ativar_offset" value="">
              </div>
              <span id="cad_ativar_preview_text" style="font-size:11px;color:#4338ca;font-weight:600;display:block;margin-top:4px"></span>
            </div>

            <div style="background:#ffffff;padding:12px;border-radius:6px;border:1px solid #e2e8f0">
              <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#1e293b">🛑 Agendar Desativação</label>
              <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px">
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 30)">+30 min</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 60)">+1h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 120)">+2h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 360)">+6h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 720)">+12h</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 1440)">+1 dia</button>
                <button type="button" class="btn-preset" onclick="setRelativeSchedule('cad_desativar', 2880)">+2 dias</button>
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <input type="datetime-local" id="input_cad_desativar_dt" name="agendado_desativar_em" class="admin-search-input" style="flex:1;font-size:13px;padding:6px 10px" onchange="syncDatetimeOffset('cad_desativar')">
                <input type="hidden" id="input_cad_desativar_offset" name="agendado_desativar_offset" value="">
              </div>
              <span id="cad_desativar_preview_text" style="font-size:11px;color:#be123c;font-weight:600;display:block;margin-top:4px"></span>
            </div>
          </div>
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
      <?php if (!empty($vagaEditar['vaga_id_externo'])): 
        $linkVagaPath = ($vagaEditar['origem'] === 'exterior' ? '/job/' : '/vaga/') . urlencode($vagaEditar['vaga_id_externo']);
      ?>
        <div style="margin-top:6px;font-size:13px;color:#475569;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span><strong>Link da vaga:</strong> <code style="background:#e2e8f0;padding:2px 6px;border-radius:4px;color:#0f172a"><?php echo htmlspecialchars($linkVagaPath, ENT_QUOTES, 'UTF-8') ?></code></span>
          <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + '<?php echo $linkVagaPath ?>'); this.innerText='Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link', 2000)" style="font-size:12px;padding:3px 10px;background:#ffffff;border:1px solid #cbd5e1;border-radius:4px;cursor:pointer;color:#334155">📋 Copiar Link</button>
          <a href="<?php echo $linkVagaPath ?>" target="_blank" style="font-size:12px;color:#4b41e1;font-weight:600;text-decoration:none">👁️ Abrir / Pré-visualizar</a>
        </div>
      <?php endif; ?>
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
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Status</label>
          <select name="status" class="admin-search-input" style="width:100%">
            <option value="ativa" <?php echo $vagaEditar['status'] === 'ativa' ? 'selected' : '' ?>>Ativa</option>
            <option value="inativa" <?php echo $vagaEditar['status'] === 'inativa' ? 'selected' : '' ?>>Inativa</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Destaque Premium 🚀</label>
          <select name="is_premium" class="admin-search-input" style="width:100%">
            <option value="0" <?php echo empty($vagaEditar['is_premium']) ? 'selected' : '' ?>>Não (Vaga Comum)</option>
            <option value="1" <?php echo !empty($vagaEditar['is_premium']) ? 'selected' : '' ?>>Sim (Vaga Premium / Destaque)</option>
          </select>
        </div>
        <div>
          <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Resumo</label>
          <textarea name="resumo" class="admin-search-input" style="width:100%;min-height:60px;resize:vertical" placeholder="Breve resumo da vaga..."><?php echo htmlspecialchars($vagaEditar['resumo'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
      </div>
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;">
        <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:#92400e;cursor:pointer;">
          <input type="checkbox" name="is_nao_listada" value="1" <?php echo !empty($vagaEditar['is_nao_listada']) ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#d97706;">
          <span>🔒 Vaga Não Listada (Acessível via link direto, mas Oculta das listas públicas e buscas do site)</span>
        </label>
      </div>

      <div>
        <label style="display:block;font-size:14px;font-weight:600;margin-bottom:4px;color:#0b1c30">Descrição (HTML)</label>
        <textarea name="descricao" class="admin-search-input" style="width:100%;min-height:200px;resize:vertical;font-family:monospace"><?php echo htmlspecialchars($vagaEditar['descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div style="background:#f8fafc;padding:16px;border-radius:8px;border:1px solid #cbd5e1;margin-top:8px">
        <h3 style="font-size:14px;font-weight:700;color:#0f172a;margin:0 0 8px 0;display:flex;align-items:center;gap:6px">
          <span>⏱️</span> Agendamento de Publicação (Relativo ao servidor)
        </h3>
        <p style="font-size:12px;color:#64748b;margin:0 0 12px 0">
          Agende quando esta vaga deve entrar ou sair do ar. Calculado em relação ao relógio do servidor (sem conflito de fuso horário).
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div style="background:#ffffff;padding:12px;border-radius:6px;border:1px solid #e2e8f0">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#1e293b">🚀 Agendar Ativação</label>
            <?php if (!empty($vagaEditar['agendado_ativar_em'])): ?>
              <div style="font-size:12px;color:#4338ca;background:#eef2ff;padding:6px 10px;border-radius:4px;margin-bottom:8px;border:1px solid #c7d2fe">
                Agendada para: <strong><?php echo date('d/m/Y H:i', strtotime($vagaEditar['agendado_ativar_em'])) ?></strong> (<?php echo agendarTempoRestante($vagaEditar['agendado_ativar_em']) ?>)
                <label style="display:block;margin-top:4px;color:#dc2626;cursor:pointer;font-weight:600">
                  <input type="checkbox" name="limpar_agendamento_ativar" value="1"> Cancelar agendamento
                </label>
              </div>
            <?php endif; ?>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px">
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 30)">+30 min</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 60)">+1h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 120)">+2h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 360)">+6h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 720)">+12h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 1440)">+1 dia</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_ativar', 2880)">+2 dias</button>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="datetime-local" id="input_edit_ativar_dt" name="agendado_ativar_em" class="admin-search-input" style="flex:1;font-size:13px;padding:6px 10px" onchange="syncDatetimeOffset('edit_ativar')">
              <input type="hidden" id="input_edit_ativar_offset" name="agendado_ativar_offset" value="">
            </div>
            <span id="edit_ativar_preview_text" style="font-size:11px;color:#4338ca;font-weight:600;display:block;margin-top:4px"></span>
          </div>

          <div style="background:#ffffff;padding:12px;border-radius:6px;border:1px solid #e2e8f0">
            <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:#1e293b">🛑 Agendar Desativação</label>
            <?php if (!empty($vagaEditar['agendado_desativar_em'])): ?>
              <div style="font-size:12px;color:#be123c;background:#fff1f2;padding:6px 10px;border-radius:4px;margin-bottom:8px;border:1px solid #fecdd3">
                Agendada para: <strong><?php echo date('d/m/Y H:i', strtotime($vagaEditar['agendado_desativar_em'])) ?></strong> (<?php echo agendarTempoRestante($vagaEditar['agendado_desativar_em']) ?>)
                <label style="display:block;margin-top:4px;color:#dc2626;cursor:pointer;font-weight:600">
                  <input type="checkbox" name="limpar_agendamento_desativar" value="1"> Cancelar agendamento
                </label>
              </div>
            <?php endif; ?>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px">
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 30)">+30 min</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 60)">+1h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 120)">+2h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 360)">+6h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 720)">+12h</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 1440)">+1 dia</button>
              <button type="button" class="btn-preset" onclick="setRelativeSchedule('edit_desativar', 2880)">+2 dias</button>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
              <input type="datetime-local" id="input_edit_desativar_dt" name="agendado_desativar_em" class="admin-search-input" style="flex:1;font-size:13px;padding:6px 10px" onchange="syncDatetimeOffset('edit_desativar')">
              <input type="hidden" id="input_edit_desativar_offset" name="agendado_desativar_offset" value="">
            </div>
            <span id="edit_desativar_preview_text" style="font-size:11px;color:#be123c;font-weight:600;display:block;margin-top:4px"></span>
          </div>
        </div>
      </div>

      <div style="display:flex;gap:12px">
        <button type="submit" name="editar_vaga" class="btn-search" style="font-size:15px;padding:12px 32px">Salvar Alterações</button>
        <a href="admin.php<?php echo $origemParam . $statusParam . $qParam . $categoriasParam ?>" class="btn-clear" style="display:inline-flex;align-items:center">Cancelar</a>
      </div>
    </form>
  </div>

  <?php elseif ($tab === 'agendadas'): ?>

  <div class="admin-header">
    <div>
      <h1>Vagas Agendadas</h1>
      <span>Vagas com ativação ou desativação programadas</span>
    </div>
  </div>

  <?php if (empty($vagasAgendadas)): ?>
    <div class="admin-empty">Nenhuma vaga agendada no momento.</div>
  <?php else: ?>
    <?php foreach ($vagasAgendadas as $ag): ?>
      <div class="admin-card" style="margin-bottom:12px">
        <div class="admin-card-header">
          <div style="flex:1">
            <div class="admin-card-title"><?php echo htmlspecialchars($ag['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-card-company"><?php echo htmlspecialchars($ag['empresa'], ENT_QUOTES, 'UTF-8') ?><?php echo $ag['localizacao'] ? ' • ' . htmlspecialchars($ag['localizacao'], ENT_QUOTES, 'UTF-8') : '' ?></div>
          </div>
        </div>
        <div class="admin-card-meta" style="margin-top:8px">
          <span style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;font-family:monospace">ID: #<?php echo (int)$ag['id'] ?></span>
          <span class="<?php echo $ag['origem'] === 'exterior' ? 'badge-origem exterior' : 'badge-origem' ?>"><?php echo $ag['origem'] === 'exterior' ? 'Exterior' : 'Brasil' ?></span>
          <span class="badge-status <?php echo $ag['status'] ?>"><?php echo $ag['status'] === 'ativa' ? 'Ativa no site' : 'Inativa no site' ?></span>

          <?php if ($ag['agendado_ativar_em']): ?>
            <span class="badge-agendado-ativar">
              🚀 <strong>Ativar em:</strong> <?php echo $ag['agendado_ativar_fmt'] ?> (<?php echo agendarTempoRestante($ag['agendado_ativar_em']) ?>)
            </span>
          <?php endif; ?>

          <?php if ($ag['agendado_desativar_em']): ?>
            <span class="badge-agendado-desativar">
              🛑 <strong>Desativar em:</strong> <?php echo $ag['agendado_desativar_fmt'] ?> (<?php echo agendarTempoRestante($ag['agendado_desativar_em']) ?>)
            </span>
          <?php endif; ?>

          <?php if (!empty($ag['tags_str'])): ?>
            <span style="font-size:12px;color:#4b41e1;font-weight:600"><?php echo htmlspecialchars($ag['tags_str'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>

        <?php if ($ag['resumo']): ?>
          <p style="font-size:14px;line-height:20px;color:#45464d;margin-top:12px"><?php echo htmlspecialchars(mb_substr($ag['resumo'], 0, 200), ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if (!empty($ag['vaga_id_externo'])): 
          $futuroUrlPath = ($ag['origem'] === 'exterior' ? '/job/' : '/vaga/') . urlencode($ag['vaga_id_externo']);
        ?>
          <div style="margin-top:14px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
            <div style="font-size:13px;color:#475569;word-break:break-all">
              <strong style="color:#1e293b">🔗 Link Futuro da Vaga:</strong>
              <code style="background:#e2e8f0;padding:3px 8px;border-radius:4px;font-size:12px;color:#0f172a;margin-left:4px"><?php echo htmlspecialchars($futuroUrlPath, ENT_QUOTES, 'UTF-8') ?></code>
            </div>
            <div style="display:flex;gap:8px">
              <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + '<?php echo $futuroUrlPath ?>'); this.innerText='Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link Futuro', 2000)" style="font-size:12px;font-weight:600;padding:6px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;cursor:pointer;color:#334155">📋 Copiar Link Futuro</button>
              <a href="<?php echo $futuroUrlPath ?>" target="_blank" style="font-size:12px;font-weight:600;padding:6px 12px;background:#4b41e1;color:#fff;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center">👁️ Pré-visualizar (Admin)</a>
            </div>
          </div>
        <?php endif; ?>

        <div class="admin-card-actions" style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <form method="post" style="margin:0">
            <input type="hidden" name="executar_agendamento_id" value="<?php echo (int)$ag['id'] ?>">
            <button type="submit" class="btn-search" style="font-size:13px;padding:8px 16px;background:#16a34a;border-color:#16a34a">⚡ Executar Agora</button>
          </form>

          <a href="admin.php?tab=editar&id=<?php echo (int)$ag['id'] ?>" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #4b41e1;color:#4b41e1;background:transparent;text-decoration:none;display:inline-flex;align-items:center">✏️ Editar Agendamento</a>

          <form method="post" style="margin:0" onsubmit="return confirm('Tem certeza que deseja cancelar este agendamento?')">
            <input type="hidden" name="cancelar_agendamento_id" value="<?php echo (int)$ag['id'] ?>">
            <button type="submit" class="btn-clear" style="font-size:13px;padding:8px 16px;color:#dc2626;border-color:#fca5a5">❌ Cancelar Agendamento</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

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

  $stmtNovas = $pdo->query("SELECT vagas.id, vagas.vaga_id_externo, vagas.titulo, vagas.empresa, vagas.localizacao, vagas.modelo_trabalho, vagas.descricao, vagas.resumo, vagas.status, vagas.origem, vagas.publicado_em, vagas.created_at, vagas.is_premium, vagas.is_favorita, vagas.is_nao_listada, DATE_FORMAT(vagas.created_at, '%d/%m/%Y %H:%i') as created_at_fmt, GROUP_CONCAT(DISTINCT c.nome_pt ORDER BY c.nome_pt SEPARATOR ', ') as tags_str FROM vagas LEFT JOIN vaga_categorias vc ON vc.vaga_id = vagas.id LEFT JOIN categorias c ON c.id = vc.categoria_id WHERE {$novasWhere} GROUP BY vagas.id ORDER BY vagas.created_at DESC");
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
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_favorita_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-favorite <?php echo !empty($v['is_favorita']) ? 'active' : '' ?>" title="<?php echo !empty($v['is_favorita']) ? 'Remover dos favoritos' : 'Favoritar vaga' ?>">
              <?php echo !empty($v['is_favorita']) ? '★ Favorita' : '☆ Favoritar' ?>
            </button>
          </form>
        </div>
        <div class="admin-card-meta">
          <span style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;font-family:monospace">ID: #<?php echo (int)$v['id'] ?></span>
          <span class="<?php echo $v['origem'] === 'exterior' ? 'badge-origem exterior' : 'badge-origem' ?>"><?php echo $v['origem'] === 'exterior' ? 'Exterior' : 'Brasil' ?></span>
          <?php if ($v['modelo_trabalho']): ?>
            <span><?php echo htmlspecialchars($v['modelo_trabalho'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <span class="badge-status <?php echo $v['status'] ?>"><?php echo $v['status'] === 'ativa' ? 'Ativa' : 'Inativa' ?></span>
          <?php if (!empty($v['is_favorita'])): ?>
            <span class="badge-favorita">⭐ Favorita</span>
          <?php endif; ?>
          <?php if (!empty($v['is_premium'])): ?>
            <span style="background:linear-gradient(135deg,#7e22ce,#a855f7);color:#fff;font-weight:700;padding:2px 8px;border-radius:4px;font-size:11px">Premium 🚀</span>
          <?php endif; ?>

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
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_favorita_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-favorite <?php echo !empty($v['is_favorita']) ? 'active' : '' ?>">
              <?php echo !empty($v['is_favorita']) ? '★ Favorita' : '☆ Favoritar' ?>
            </button>
          </form>
          <a href="admin.php?tab=editar&id=<?php echo (int)$v['id'] ?>" class="btn-action-edit">✏️ Editar</a>
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle <?php echo $v['status'] === 'ativa' ? 'inativar' : 'ativar' ?>"><?php echo $v['status'] === 'ativa' ? '🔴 Inativar' : '🟢 Ativar' ?></button>
          </form>
          <?php if (!empty($v['vaga_id_externo'])): 
            $linkNovasPath = ($v['origem'] === 'exterior' ? '/job/' : '/vaga/') . urlencode($v['vaga_id_externo']);
            $rawExpTextNovas = !empty($v['resumo']) ? $v['resumo'] : (!empty($v['descricao']) ? $v['descricao'] : '');
            $cleanExpTextNovas = trim(preg_replace('/\s+/', ' ', strip_tags($rawExpTextNovas)));
            $excerpt200Novas = mb_substr($cleanExpTextNovas, 0, 200);
            if (mb_strlen($cleanExpTextNovas) > 200) { $excerpt200Novas .= '...'; }
          ?>
            <a href="<?php echo $linkNovasPath ?>" target="_blank" class="btn-action-view">
              👁️ <?php echo ($v['status'] === 'ativa') ? 'Ver no site' : 'Pré-visualizar' ?>
            </a>
            <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + '<?php echo $linkNovasPath ?>'); this.innerText='Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link', 2000)" class="btn-action-copy">📋 Copiar Link</button>
            <button type="button" class="btn-action-export" data-titulo="<?php echo htmlspecialchars($v['titulo'], ENT_QUOTES, 'UTF-8') ?>" data-modelo="<?php echo htmlspecialchars($v['modelo_trabalho'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-local="<?php echo htmlspecialchars($v['localizacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-resumo="<?php echo htmlspecialchars($excerpt200Novas, ENT_QUOTES, 'UTF-8') ?>" data-path="<?php echo htmlspecialchars($linkNovasPath, ENT_QUOTES, 'UTF-8') ?>" data-origem="<?php echo htmlspecialchars($v['origem'] ?? '', ENT_QUOTES, 'UTF-8') ?>" onclick="exportarVaga(this)">📤 Exportar Vaga</button>
          <?php endif; ?>
          <form method="post" style="margin:0" onsubmit="return confirm('Remover esta vaga da lista de novas?')">
            <input type="hidden" name="remover_vaga_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle inativar">Remover</button>
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

  <?php elseif ($tab === 'por-categorias'): ?>

  <div class="admin-header">
    <div>
      <h1>Pesquisar Vagas Ativas por Categoria</h1>
      <span>Clique em qualquer uma das categorias abaixo para listar todas as vagas ativas correspondentes</span>
    </div>
  </div>

  <div class="cat-grid-wrapper">
    <?php foreach ($categoriasAtivasList as $catItem): ?>
      <a href="admin.php?tab=por-categorias&cat=<?php echo urlencode($catItem['slug']) ?>" 
         class="cat-tile <?php echo $catSelected === $catItem['slug'] ? 'active' : '' ?>">
        <div class="cat-tile-name"><?php echo htmlspecialchars($catItem['nome_pt'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="cat-tile-count"><?php echo (int)$catItem['total_ativas'] ?> vaga(s) ativas</div>
      </a>
    <?php endforeach; ?>
    <a href="admin.php?tab=por-categorias&cat=sem-categoria" 
       class="cat-tile cat-tile-sem-cat <?php echo $catSelected === 'sem-categoria' ? 'active' : '' ?>">
      <div class="cat-tile-name">Sem Categoria</div>
      <div class="cat-tile-count"><?php echo $totalSemCategoriaAtivas ?> vaga(s) ativas</div>
    </a>
  </div>

  <?php if ($catSelected === ''): ?>
    <div class="admin-empty" style="background:#fff;border:1px solid #c6c6cd;border-radius:0.75rem;padding:48px 24px;margin-top:16px;">
      <p style="font-size:16px;font-weight:600;color:#0b1c30;margin-bottom:4px">Selecione uma Categoria</p>
      <p style="font-size:14px;color:#45464d;margin:0">Clique em qualquer um dos blocos acima para exibir as vagas ativas correspondentes.</p>
    </div>
  <?php else: ?>

    <div class="admin-header" style="margin-top:24px;margin-bottom:16px">
      <div>
        <h2 style="font-size:1.25rem;font-weight:700;margin:0">
          Vagas Ativas em <span style="color:#4b41e1"><?php echo htmlspecialchars($catNomeAtual, ENT_QUOTES, 'UTF-8') ?></span>
        </h2>
        <span style="font-size:14px;color:#45464d"><?php echo $totalVagasCat ?> vaga(s) encontrada(s)</span>
      </div>
    </div>

    <form class="admin-search" method="get">
      <input type="hidden" name="tab" value="por-categorias">
      <input type="hidden" name="cat" value="<?php echo htmlspecialchars($catSelected, ENT_QUOTES, 'UTF-8') ?>">
      <input type="search" name="q" class="admin-search-input" placeholder="Filtrar por ID, título, empresa ou localização nesta categoria..." value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>">
      <button type="submit" class="btn-search">Buscar</button>
      <?php if ($searchQuery !== ''): ?>
        <a href="admin.php?tab=por-categorias&cat=<?php echo urlencode($catSelected) ?>" class="btn-clear">Limpar busca</a>
      <?php endif; ?>
    </form>

    <div class="batch-bar" id="batch-bar-top"></div>

    <?php if (empty($vagasPorCat)): ?>
      <div class="admin-empty">Nenhuma vaga ativa encontrada para a categoria "<?php echo htmlspecialchars($catNomeAtual, ENT_QUOTES, 'UTF-8') ?>".</div>
    <?php else: ?>
      <?php foreach ($vagasPorCat as $v): ?>
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
            <span style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;font-family:monospace">ID: #<?php echo (int)$v['id'] ?></span>
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
            <a href="admin.php?tab=editar&id=<?php echo (int)$v['id'] ?>" style="font-size:13px;font-weight:600;padding:7px 18px;border-radius:0.5rem;border:1px solid #4b41e1;color:#4b41e1;background:transparent;text-decoration:none;display:inline-flex;align-items:center">Editar</a>
            <form method="post" style="margin:0">
              <input type="hidden" name="toggle_id" value="<?php echo (int)$v['id'] ?>">
              <button type="submit" class="btn-toggle <?php echo $v['status'] === 'ativa' ? 'inativar' : 'ativar' ?>"><?php echo $v['status'] === 'ativa' ? 'Inativar' : 'Ativar' ?></button>
            </form>
            <?php if (!empty($v['vaga_id_externo'])): 
              $linkCatPath = ($v['origem'] === 'exterior' ? '/job/' : '/vaga/') . urlencode($v['vaga_id_externo']);
              $rawExpTextCat = !empty($v['resumo']) ? $v['resumo'] : (!empty($v['descricao']) ? $v['descricao'] : '');
              $cleanExpTextCat = trim(preg_replace('/\s+/', ' ', strip_tags($rawExpTextCat)));
              $excerpt200Cat = mb_substr($cleanExpTextCat, 0, 200);
              if (mb_strlen($cleanExpTextCat) > 200) { $excerpt200Cat .= '...'; }
            ?>
              <a href="<?php echo $linkCatPath ?>" target="_blank" style="font-size:13px;font-weight:500;color:#4b41e1;padding:7px 18px;border:1px solid #4b41e1;border-radius:0.5rem;text-decoration:none;display:inline-flex;align-items:center">
                <?php echo ($v['status'] === 'ativa') ? 'Ver no site' : 'Pré-visualizar Link' ?>
              </a>
              <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + '<?php echo $linkCatPath ?>'); this.innerText='Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link', 2000)" style="font-size:12px;padding:7px 12px;background:#ffffff;border:1px solid #cbd5e1;border-radius:0.5rem;cursor:pointer;color:#334155">📋 Copiar Link</button>
              <button type="button" class="btn-action-export" data-titulo="<?php echo htmlspecialchars($v['titulo'], ENT_QUOTES, 'UTF-8') ?>" data-modelo="<?php echo htmlspecialchars($v['modelo_trabalho'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-local="<?php echo htmlspecialchars($v['localizacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-resumo="<?php echo htmlspecialchars($excerpt200Cat, ENT_QUOTES, 'UTF-8') ?>" data-path="<?php echo htmlspecialchars($linkCatPath, ENT_QUOTES, 'UTF-8') ?>" data-origem="<?php echo htmlspecialchars($v['origem'] ?? '', ENT_QUOTES, 'UTF-8') ?>" onclick="exportarVaga(this)">📤 Exportar Vaga</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if ($totalPagesCat > 1): ?>
        <div class="admin-pagination">
          <?php if ($pageCat > 1): ?>
            <a href="?tab=por-categorias&cat=<?php echo urlencode($catSelected) ?>&page=<?php echo $pageCat - 1 ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>">&laquo;</a>
          <?php endif; ?>
          <?php
          $startPage = max(1, $pageCat - 4);
          $endPage = min($totalPagesCat, $pageCat + 4);
          for ($i = $startPage; $i <= $endPage; $i++): ?>
            <?php if ($i === $pageCat): ?>
              <span class="current"><?php echo $i ?></span>
            <?php else: ?>
              <a href="?tab=por-categorias&cat=<?php echo urlencode($catSelected) ?>&page=<?php echo $i ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>"><?php echo $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>
          <?php if ($pageCat < $totalPagesCat): ?>
            <a href="?tab=por-categorias&cat=<?php echo urlencode($catSelected) ?>&page=<?php echo $pageCat + 1 ?><?php echo $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>">&raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>

    <div class="batch-bar" id="batch-bar-bottom"></div>

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
      <h1><?php echo $tab === 'favoritas' ? '⭐ Vagas Favoritas' : 'Vagas' ?></h1>
      <span><?php echo $totalVagas ?> vaga(s) encontrada(s)</span>
    </div>
  </div>

  <div class="admin-subfilter-bar">
    <div class="filter-group">
      <span class="filter-label">Status:</span>
      <a class="filter-btn <?php echo ($tab !== 'favoritas' && $statusFilter === '') ? 'active' : '' ?>" href="admin.php?<?php echo $origemParam . $qParam ?>">Todas (<?php echo $totalVagas ?>)</a>
      <a class="filter-btn <?php echo ($tab !== 'favoritas' && $statusFilter === 'ativa') ? 'active' : '' ?>" href="admin.php?status=ativa<?php echo $origemParam . $qParam ?>">Ativas (<?php echo $totalAtivas ?>)</a>
      <a class="filter-btn <?php echo ($tab !== 'favoritas' && $statusFilter === 'inativa') ? 'active' : '' ?>" href="admin.php?status=inativa<?php echo $origemParam . $qParam ?>">Inativas (<?php echo $totalInativas ?>)</a>
      <a class="filter-btn <?php echo ($tab === 'favoritas' || $statusFilter === 'favorita') ? 'active' : '' ?>" href="admin.php?tab=favoritas<?php echo $origemParam . $qParam ?>">⭐ Favoritas (<?php echo $totalFavoritasCount ?>)</a>
    </div>
    <div class="filter-group">
      <span class="filter-label">Origem:</span>
      <a class="filter-btn <?php echo $origemFilter === '' ? 'active' : '' ?>" href="admin.php?<?php echo ($tab === 'favoritas' ? 'tab=favoritas' : $statusParam) . $qParam ?>">Todas</a>
      <a class="filter-btn <?php echo $origemFilter === 'nacional' ? 'active' : '' ?>" href="admin.php?origem=nacional<?php echo ($tab === 'favoritas' ? '&tab=favoritas' : $statusParam) . $qParam ?>">🇧🇷 Brasil</a>
      <a class="filter-btn <?php echo $origemFilter === 'exterior' ? 'active' : '' ?>" href="admin.php?origem=exterior<?php echo ($tab === 'favoritas' ? '&tab=favoritas' : $statusParam) . $qParam ?>">🌎 Exterior</a>
    </div>
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
        <?php if ($tab === 'favoritas'): ?>
          <input type="hidden" name="tab" value="favoritas">
        <?php endif; ?>
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
          <a href="admin.php<?php echo ($tab === 'favoritas' ? '?tab=favoritas' : '') . $origemParam . $statusParam . $qParam ?>" class="btn-clear">Limpar filtros</a>
        <?php endif; ?>
      </div>
    </form>
  </details>

  <form class="admin-search" method="get">
    <?php if ($tab === 'favoritas'): ?>
      <input type="hidden" name="tab" value="favoritas">
    <?php endif; ?>
    <input type="search" name="q" class="admin-search-input" placeholder="Buscar por ID, título, empresa ou local..." value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" autofocus>
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
      <a href="admin.php<?php echo ($tab === 'favoritas' ? '?tab=favoritas' : '') . $origemParam . $statusParam ?>" class="btn-clear">Limpar</a>
    <?php endif; ?>
  </form>

  <div class="batch-bar" id="batch-bar-top"></div>

  <?php if (empty($vagas)): ?>
    <div class="admin-empty"><?php echo $tab === 'favoritas' ? 'Nenhuma vaga favoritada ainda. Clique em "☆ Favoritar" em qualquer vaga para salvá-la aqui.' : 'Nenhuma vaga encontrada.' ?></div>
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
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_favorita_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-favorite <?php echo !empty($v['is_favorita']) ? 'active' : '' ?>" title="<?php echo !empty($v['is_favorita']) ? 'Remover dos favoritos' : 'Favoritar vaga' ?>">
              <?php echo !empty($v['is_favorita']) ? '★ Favorita' : '☆ Favoritar' ?>
            </button>
          </form>
        </div>
        <div class="admin-card-meta">
          <span style="background:#e2e8f0;color:#0f172a;font-weight:700;padding:2px 8px;border-radius:4px;font-size:12px;font-family:monospace">ID: #<?php echo (int)$v['id'] ?></span>
          <span class="<?php echo $v['origem'] === 'exterior' ? 'badge-origem exterior' : 'badge-origem' ?>"><?php echo $v['origem'] === 'exterior' ? 'Exterior' : 'Brasil' ?></span>
          <?php if ($v['modelo_trabalho']): ?>
            <span class="badge badge-<?php echo htmlspecialchars(strtolower($v['modelo_trabalho']), ENT_QUOTES, 'UTF-8') === 'remote' ? 'remote' : (strtolower($v['modelo_trabalho']) === 'hybrid' ? 'hybrid' : 'onsite') ?>"><?php echo htmlspecialchars($v['modelo_trabalho'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
          <span class="badge-status <?php echo $v['status'] ?>"><?php echo $v['status'] === 'ativa' ? 'Ativa' : 'Inativa' ?></span>
          <?php if (!empty($v['is_favorita'])): ?>
            <span class="badge-favorita">⭐ Favorita</span>
          <?php endif; ?>
          <?php if (!empty($v['is_nao_listada'])): ?>
            <span style="background:#fef3c7;color:#92400e;font-weight:700;padding:2px 8px;border-radius:4px;font-size:11px;border:1px solid #fde68a;">🔒 Não Listada</span>
          <?php endif; ?>
          <?php if (!empty($v['is_premium'])): ?>
            <span style="background:linear-gradient(135deg,#7e22ce,#a855f7);color:#fff;font-weight:700;padding:2px 8px;border-radius:4px;font-size:11px">Premium 🚀</span>
          <?php endif; ?>
          <?php if (!empty($v['agendado_ativar_em'])): ?>
            <span class="badge-agendado-ativar">🚀 Ativar <?php echo agendarTempoRestante($v['agendado_ativar_em']) ?></span>
          <?php endif; ?>
          <?php if (!empty($v['agendado_desativar_em'])): ?>
            <span class="badge-agendado-desativar">🛑 Desativar <?php echo agendarTempoRestante($v['agendado_desativar_em']) ?></span>
          <?php endif; ?>
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
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_favorita_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-favorite <?php echo !empty($v['is_favorita']) ? 'active' : '' ?>">
              <?php echo !empty($v['is_favorita']) ? '★ Favorita' : '☆ Favoritar' ?>
            </button>
          </form>
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_nao_listada_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle" style="<?php echo !empty($v['is_nao_listada']) ? 'background:#d97706;color:#fff;border-color:#b45309;' : 'background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;' ?>" title="<?php echo !empty($v['is_nao_listada']) ? 'Tornar visível nas listas do site' : 'Ocultar das listas públicas do site' ?>">
              <?php echo !empty($v['is_nao_listada']) ? '🔒 Não Listada' : '👁️ Listada' ?>
            </button>
          </form>
          <a href="admin.php?tab=editar&id=<?php echo (int)$v['id'] . $origemParam . $statusParam . $qParam . $categoriasParam ?>" class="btn-action-edit">✏️ Editar</a>
          <form method="post" style="margin:0">
            <input type="hidden" name="toggle_id" value="<?php echo (int)$v['id'] ?>">
            <button type="submit" class="btn-toggle <?php echo $v['status'] === 'ativa' ? 'inativar' : 'ativar' ?>"><?php echo $v['status'] === 'ativa' ? '🔴 Inativar' : '🟢 Ativar' ?></button>
          </form>
          <?php if (!empty($v['agendado_ativar_em']) || !empty($v['agendado_desativar_em'])): ?>
            <form method="post" style="margin:0">
              <input type="hidden" name="executar_agendamento_id" value="<?php echo (int)$v['id'] ?>">
              <button type="submit" class="btn-toggle ativar" style="font-size:12px;padding:6px 12px">⚡ Executar Agendamento</button>
            </form>
          <?php endif; ?>
          <?php if (!empty($v['vaga_id_externo'])): 
            $linkListPath = ($v['origem'] === 'exterior' ? '/job/' : '/vaga/') . urlencode($v['vaga_id_externo']);
            $rawExpTextList = !empty($v['resumo']) ? $v['resumo'] : (!empty($v['descricao']) ? $v['descricao'] : '');
            $cleanExpTextList = trim(preg_replace('/\s+/', ' ', strip_tags($rawExpTextList)));
            $excerpt200List = mb_substr($cleanExpTextList, 0, 200);
            if (mb_strlen($cleanExpTextList) > 200) { $excerpt200List .= '...'; }
          ?>
            <a href="<?php echo $linkListPath ?>" target="_blank" class="btn-action-view">
              👁️ <?php echo ($v['status'] === 'ativa') ? 'Ver no site' : 'Pré-visualizar' ?>
            </a>
            <button type="button" onclick="navigator.clipboard.writeText(window.location.origin + '<?php echo $linkListPath ?>'); this.innerText='Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link', 2000)" class="btn-action-copy">📋 Copiar Link</button>
            <button type="button" class="btn-action-export" data-titulo="<?php echo htmlspecialchars($v['titulo'], ENT_QUOTES, 'UTF-8') ?>" data-modelo="<?php echo htmlspecialchars($v['modelo_trabalho'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-local="<?php echo htmlspecialchars($v['localizacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-resumo="<?php echo htmlspecialchars($excerpt200List, ENT_QUOTES, 'UTF-8') ?>" data-path="<?php echo htmlspecialchars($linkListPath, ENT_QUOTES, 'UTF-8') ?>" data-origem="<?php echo htmlspecialchars($v['origem'] ?? '', ENT_QUOTES, 'UTF-8') ?>" onclick="exportarVaga(this)">📤 Exportar Vaga</button>
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
    <button type="button" class="btn-favorite active" onclick="batchToggle('favoritar')" style="padding:6px 12px;font-size:12px">⭐ Favoritar</button>
    <button type="button" class="btn-favorite" onclick="batchToggle('desfavoritar')" style="padding:6px 12px;font-size:12px">☆ Desfavoritar</button>
    <span style="border-left:1px solid #ccc;height:24px;margin:0 4px"></span>
    <button type="button" class="btn-toggle inativar" onclick="batchToggle('inativar')">Inativar Selecionadas</button>
    <button type="button" class="btn-toggle ativar" onclick="batchToggle('ativar')">Ativar Selecionadas</button>
    <button type="button" class="btn-toggle" onclick="batchToggle('nao_listada')" style="padding:6px 12px;font-size:12px;background:#d97706;color:#fff;border-color:#b45309;">🔒 Marcar Não Listada</button>
    <button type="button" class="btn-toggle" onclick="batchToggle('listada')" style="padding:6px 12px;font-size:12px;background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">👁️ Marcar Listada</button>
    <button type="button" class="btn-toggle inativar" onclick="batchToggle('remover')">Remover Selecionadas</button>
    <span style="border-left:1px solid #ccc;height:24px;margin:0 4px"></span>
    <input type="datetime-local" class="batch-schedule-dt" style="font-size:12px;padding:4px 8px;border:1px solid #ccc;border-radius:4px;color:#0b1c30;background:#fff" title="Escolha a data e a hora do agendamento">
    <button type="button" class="btn-toggle ativar" onclick="batchSchedule('agendar_ativar')" style="padding:6px 12px;font-size:12px">🚀 Agendar Ativação</button>
    <button type="button" class="btn-toggle inativar" onclick="batchSchedule('agendar_desativar')" style="padding:6px 12px;font-size:12px">🛑 Agendar Desativação</button>
    <button type="button" class="btn-toggle inativar" onclick="batchToggle('cancelar_agendamento')" style="padding:6px 12px;font-size:12px;color:#dc2626;border-color:#fca5a5">❌ Cancelar Agendamento</button>
    <span style="border-left:1px solid #ccc;height:24px;margin:0 4px"></span>
    <select id="batch-cat-select" multiple style="max-width:180px;font-size:12px;padding:4px;border:1px solid #ccc;border-radius:4px;min-height:28px">
      <?php foreach ($todasCategorias as $cat): ?>
        <?php if ($cat['slug'] !== 'sem-categoria'): ?>
          <option value="<?php echo $cat['slug'] ?>"><?php echo htmlspecialchars($cat['nome_pt'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endif; ?>
      <?php endforeach; ?>
    </select>
    <button type="button" class="btn-toggle ativar" onclick="batchCategorize()" style="padding:6px 12px;font-size:12px">Categorizar</button>
    <button type="button" class="btn-toggle inativar" onclick="batchRemoveCats()" style="padding:6px 12px;font-size:12px">Remover Categorias</button>
  </template>

  <form id="batch-form" method="post" style="display:none">
    <input type="hidden" name="batch_ids" id="batch-ids">
    <input type="hidden" name="batch_action" id="batch-action">
    <input type="hidden" name="batch_offset_minutes" id="batch-offset-minutes">
    <input type="hidden" name="batch_schedule_datetime" id="batch-schedule-datetime">
    <input type="hidden" name="batch_categorize" id="batch-cats">
    <input type="hidden" name="batch_remove_cats" id="batch-remove-cats">
  </form>
</main>

<script>
(function() {
  window.setRelativeSchedule = function(type, minutes) {
    var hiddenInput = document.getElementById('input_' + type + '_offset');
    var previewText = document.getElementById(type + '_preview_text');
    var dtInput = document.getElementById('input_' + type + '_dt');

    if (hiddenInput) hiddenInput.value = minutes;

    if (dtInput) {
      var targetDate = new Date(Date.now() + minutes * 60000);
      var isoStr = new Date(targetDate.getTime() - (targetDate.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
      dtInput.value = isoStr;
    }

    var desc = minutes < 60 ? minutes + ' min' : (minutes < 1440 ? (minutes / 60) + 'h' : (minutes / 1440) + 'd');
    if (previewText) previewText.innerText = '⚡ Agendado para daqui a ' + desc + ' (relativo ao relógio do servidor).';
  };

  window.syncDatetimeOffset = function(type) {
    var dtInput = document.getElementById('input_' + type + '_dt');
    var hiddenInput = document.getElementById('input_' + type + '_offset');
    var previewText = document.getElementById(type + '_preview_text');

    if (!dtInput || !dtInput.value) {
      if (hiddenInput) hiddenInput.value = '';
      if (previewText) previewText.innerText = '';
      return;
    }

    var selectedTime = new Date(dtInput.value).getTime();
    var nowTime = new Date().getTime();
    var diffMinutes = Math.round((selectedTime - nowTime) / 60000);

    if (diffMinutes <= 0) {
      alert('Por favor, selecione uma data e hora no futuro.');
      dtInput.value = '';
      if (hiddenInput) hiddenInput.value = '';
      if (previewText) previewText.innerText = '';
      return;
    }

    if (hiddenInput) hiddenInput.value = diffMinutes;
    var desc = diffMinutes < 60 ? diffMinutes + ' min' : (diffMinutes < 1440 ? Math.round(diffMinutes / 60) + 'h' : Math.round(diffMinutes / 1440) + 'd');
    if (previewText) previewText.innerText = '⚡ Ajustado para daqui a ~' + desc + ' em relação ao relógio do servidor.';
  };

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
  if (tpl && topBar) topBar.appendChild(tpl.content.cloneNode(true));
  if (tpl && bottomBar) bottomBar.appendChild(tpl.content.cloneNode(true));

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
    if (!ids.length) return alert('Selecione ao menos uma vaga.');
    var msgs = { inativar: 'Inativar', ativar: 'Ativar', remover: 'Remover da lista de novas', cancelar_agendamento: 'Cancelar agendamentos de', favoritar: 'Favoritar', desfavoritar: 'Desfavoritar' };
    var msg = msgs[action] || action;
    if (!confirm(msg + ' ' + ids.length + ' vaga(s)?')) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = action;
    document.getElementById('batch-cats').value = '';
    document.getElementById('batch-remove-cats').value = '';
    document.getElementById('batch-form').submit();
  };

  window.batchSchedule = function(action) {
    var ids = [];
    document.querySelectorAll('.batch-check:checked').forEach(function(cb) {
      ids.push(cb.value);
    });
    if (!ids.length) return alert('Selecione ao menos uma vaga.');
    var dtInputs = document.querySelectorAll('.batch-schedule-dt');
    var dtVal = '';
    dtInputs.forEach(function(input) { if (input.value) dtVal = input.value; });
    if (!dtVal) return alert('Por favor, selecione a data e a hora para o agendamento.');
    var formattedDate = new Date(dtVal).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
    var actionTitle = action === 'agendar_ativar' ? 'ativar' : 'desativar';
    if (!confirm('Agendar para ' + actionTitle + ' ' + ids.length + ' vaga(s) em ' + formattedDate + '?')) return;
    document.getElementById('batch-ids').value = ids.join(',');
    document.getElementById('batch-action').value = action;
    document.getElementById('batch-schedule-datetime').value = dtVal;
    document.getElementById('batch-offset-minutes').value = '';
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

  window.exportarVaga = function(btn) {
    var titulo = btn.getAttribute('data-titulo') || '';
    var modelo = btn.getAttribute('data-modelo') || 'Não especificado';
    var local = btn.getAttribute('data-local') || 'Não especificado';
    var resumo = btn.getAttribute('data-resumo') || '';
    var path = btn.getAttribute('data-path') || '';
    var origem = btn.getAttribute('data-origem') || '';
    var fullUrl = window.location.origin + path;

    var localLinha = (origem === 'exterior') ? '🇺🇸 USA Only' : ('🌎 ' + local);

    var text = '👔 ' + titulo + '\n' +
               '🌐 ' + modelo + '\n' +
               localLinha + '\n\n' +
               '"' + resumo + '"\n\n' +
               'Descrição completa e form de aplicação\n' +
               '🔗 ' + fullUrl;

    navigator.clipboard.writeText(text).then(function() {
      var originalText = btn.innerHTML;
      btn.innerHTML = '📤 Copiado!';
      btn.classList.add('copied');
      setTimeout(function() {
        btn.innerHTML = originalText;
        btn.classList.remove('copied');
      }, 2000);
    }).catch(function(err) {
      alert('Erro ao copiar conteúdo da vaga: ' + err);
    });
  };
})();
</script>

<?php endif; ?>
</body>
</html>
