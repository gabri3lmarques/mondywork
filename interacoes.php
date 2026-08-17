<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/App/Autoloader.php';
require_once __DIR__ . '/lib/Database.php';

use App\Core\Database;
use App\Repositories\InteracaoRepository;
use App\Services\AuthService;
use App\Services\AvatarService;

function formatTempoRelativo(string $datetime, string $lang = 'pt'): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($lang === 'en') {
        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            $mins = max(1, (int) floor($diff / 60));
            return "{$mins} min" . ($mins > 1 ? 's' : '') . " ago";
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return "{$hours} hour" . ($hours > 1 ? 's' : '') . " ago";
        }
        if ($diff < 172800) {
            return 'yesterday at ' . date('h:i A', $timestamp);
        }
        if ($diff < 604800) {
            $days = (int) floor($diff / 86400);
            return "{$days} day" . ($days > 1 ? 's' : '') . " ago";
        }
        return date('M d, Y \a\t h:i A', $timestamp);
    }

    if ($diff < 60) {
        return 'agora mesmo';
    }
    if ($diff < 3600) {
        $mins = max(1, (int) floor($diff / 60));
        return "há {$mins} min" . ($mins > 1 ? 's' : '');
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return "há {$hours} hora" . ($hours > 1 ? 's' : '');
    }
    if ($diff < 172800) {
        return 'ontem às ' . date('H:i', $timestamp);
    }
    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return "há {$days} dias";
    }
    return date('d/m/Y \à\s H:i', $timestamp);
}

try {
    $pdo = Database::getConnection();
    setupSchema($pdo);

    $repo = new InteracaoRepository($pdo);
    $currentUser = AuthService::getLoggedUser();
    $isAdmin = !empty($_SESSION['admin_logged_in']);

    $lang = strtolower(trim($_REQUEST['lang'] ?? ''));
    if ($lang !== 'en') {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, '/usa/') !== false || strpos($referer, '/job/') !== false) {
            $lang = 'en';
        } else {
            $lang = 'pt';
        }
    }
    $isAdmin = !empty($_SESSION['admin_logged_in']);

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'get_batch':
            $rawIds = $_GET['vaga_ids'] ?? '';
            $ids = array_values(array_filter(array_map('intval', explode(',', $rawIds))));
            if (empty($ids)) {
                echo json_encode(['success' => true, 'data' => [], 'current_user' => $currentUser]);
                break;
            }

            $inSql = implode(',', $ids);

            // Reações agrupadas
            $stmtR = $pdo->query("
                SELECT vaga_id, tipo, COUNT(*) as total 
                FROM vaga_reacoes 
                WHERE vaga_id IN ({$inSql}) 
                GROUP BY vaga_id, tipo
            ");
            $reactionsRows = $stmtR->fetchAll(PDO::FETCH_ASSOC);

            // Reações do usuário atual
            $userReactions = [];
            if ($currentUser) {
                $stmtU = $pdo->prepare("
                    SELECT vaga_id, tipo 
                    FROM vaga_reacoes 
                    WHERE vaga_id IN ({$inSql}) AND usuario_id = :uid
                ");
                $stmtU->execute([':uid' => $currentUser['id']]);
                while ($row = $stmtU->fetch(PDO::FETCH_ASSOC)) {
                    $userReactions[(int)$row['vaga_id']] = $row['tipo'];
                }
            }

            // Contagem de comentários
            $stmtC = $pdo->query("
                SELECT vaga_id, COUNT(*) as total 
                FROM vaga_comentarios 
                WHERE vaga_id IN ({$inSql}) 
                GROUP BY vaga_id
            ");
            $commentsCount = [];
            while ($row = $stmtC->fetch(PDO::FETCH_ASSOC)) {
                $commentsCount[(int)$row['vaga_id']] = (int)$row['total'];
            }

            $batchData = [];
            foreach ($ids as $vid) {
                $batchData[$vid] = [
                    'reactions'      => ['like' => 0, 'dislike' => 0, 'love' => 0, 'angry' => 0, 'total' => 0],
                    'user_reaction'  => $userReactions[$vid] ?? null,
                    'comments_count' => (int) ($commentsCount[$vid] ?? 0)
                ];
            }
            foreach ($reactionsRows as $r) {
                $vid = (int) $r['vaga_id'];
                $tipo = $r['tipo'];
                if (isset($batchData[$vid]['reactions'][$tipo])) {
                    $batchData[$vid]['reactions'][$tipo] = (int) $r['total'];
                    $batchData[$vid]['reactions']['total'] += (int) $r['total'];
                }
            }

            echo json_encode([
                'success'      => true,
                'data'         => $batchData,
                'current_user' => $currentUser
            ]);
            break;

        case 'get':
            $vagaId = (int) ($_GET['vaga_id'] ?? 0);
            if ($vagaId <= 0) {
                throw new Exception('ID da vaga inválido.');
            }

            $currentUserId = $currentUser ? (int) $currentUser['id'] : null;
            $reactions = $repo->getReactionsSummary($vagaId, $currentUserId);
            $rawComments = $repo->getComments($vagaId);

            $comments = [];
            foreach ($rawComments as $c) {
                $author = [
                    'nome' => $c['usuario_nome'],
                    'foto' => $c['usuario_foto']
                ];
                $comments[] = [
                    'id'            => (int) $c['id'],
                    'comentario'    => htmlspecialchars($c['comentario'], ENT_QUOTES, 'UTF-8'),
                    'autor_nome'    => htmlspecialchars($c['usuario_nome'], ENT_QUOTES, 'UTF-8'),
                    'avatar_html'   => AvatarService::renderAvatar($author, 36),
                    'tempo'         => formatTempoRelativo($c['created_at'], $lang),
                    'created_at'    => $c['created_at'],
                    'pode_excluir'  => ($currentUser && (int)$c['usuario_id'] === (int)$currentUser['id']) || $isAdmin
                ];
            }

            echo json_encode([
                'success'        => true,
                'reactions'      => $reactions['counts'],
                'user_reaction'  => $reactions['user_reaction'],
                'comments'       => $comments,
                'comments_count' => count($comments),
                'logged_in'      => (bool) $currentUser,
                'current_user'   => $currentUser
            ]);
            break;

        case 'react':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido.');
            }
            if (!$currentUser) {
                http_response_code(401);
                throw new Exception($lang === 'en' ? 'You must be signed in to react to this job.' : 'Você precisa estar logado para reagir à vaga.');
            }

            $vagaId = (int) ($_POST['vaga_id'] ?? 0);
            $tipo   = trim($_POST['tipo'] ?? '');

            if ($vagaId <= 0) {
                throw new Exception($lang === 'en' ? 'Invalid job ID.' : 'ID da vaga inválido.');
            }

            $result = $repo->toggleReaction($vagaId, (int) $currentUser['id'], $tipo);

            echo json_encode([
                'success'       => true,
                'reactions'     => $result['counts'],
                'user_reaction' => $result['user_reaction']
            ]);
            break;

        case 'comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido.');
            }
            if (!$currentUser) {
                http_response_code(401);
                throw new Exception($lang === 'en' ? 'You must be signed in to comment.' : 'Você precisa estar logado para comentar.');
            }

            $vagaId     = (int) ($_POST['vaga_id'] ?? 0);
            $comentario = trim($_POST['comentario'] ?? '');

            if ($vagaId <= 0) {
                throw new Exception($lang === 'en' ? 'Invalid job ID.' : 'ID da vaga inválido.');
            }

            $commentId = $repo->addComment($vagaId, (int) $currentUser['id'], $comentario);

            $author = [
                'nome' => $currentUser['nome'],
                'foto' => $currentUser['foto'] ?? null
            ];

            echo json_encode([
                'success' => true,
                'comment' => [
                    'id'           => $commentId,
                    'comentario'   => htmlspecialchars($comentario, ENT_QUOTES, 'UTF-8'),
                    'autor_nome'   => htmlspecialchars($currentUser['nome'], ENT_QUOTES, 'UTF-8'),
                    'avatar_html'  => AvatarService::renderAvatar($author, 36),
                    'tempo'        => formatTempoRelativo(date('Y-m-d H:i:s'), $lang),
                    'pode_excluir' => true
                ]
            ]);
            break;

        case 'delete_comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método não permitido.');
            }
            if (!$currentUser && !$isAdmin) {
                http_response_code(401);
                throw new Exception('Você não tem permissão para excluir este comentário.');
            }

            $commentId = (int) ($_POST['id'] ?? 0);
            if ($commentId <= 0) {
                throw new Exception('ID do comentário inválido.');
            }

            $currentUserId = $currentUser ? (int) $currentUser['id'] : 0;
            $deleted = $repo->deleteComment($commentId, $currentUserId, $isAdmin);

            if (!$deleted) {
                throw new Exception('Não foi possível excluir o comentário.');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Comentário excluído com sucesso.'
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ação inválida.']);
            break;
    }
} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
