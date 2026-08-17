<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/App/Autoloader.php';
require_once __DIR__ . '/lib/Database.php';

use App\Core\Database;
use App\Services\AuthService;
use App\Services\AvatarService;

try {
    $pdo = Database::getConnection();
    setupSchema($pdo);

    $authService = new AuthService();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    $lang = strtolower(trim($_REQUEST['lang'] ?? ''));
    if ($lang !== 'en') {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, '/usa/') !== false || strpos($referer, '/job/') !== false) {
            $lang = 'en';
        } else {
            $lang = 'pt';
        }
    }

    switch ($action) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception($lang === 'en' ? 'Method not allowed.' : 'Método não permitido.');
            }
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $fotoFile = $_FILES['foto'] ?? null;

            $user = $authService->register($nome, $email, $senha, $fotoFile, $lang);
            $avatarHtml = AvatarService::renderAvatar($user, 36);

            echo json_encode([
                'success' => true,
                'message' => $lang === 'en' ? 'Account created successfully!' : 'Cadastro realizado com sucesso!',
                'user'    => $user,
                'avatar_html' => $avatarHtml
            ]);
            break;

        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception($lang === 'en' ? 'Method not allowed.' : 'Método não permitido.');
            }
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';

            $user = $authService->login($email, $senha, $lang);
            $avatarHtml = AvatarService::renderAvatar($user, 36);

            echo json_encode([
                'success' => true,
                'message' => $lang === 'en' ? 'Logged in successfully!' : 'Login realizado com sucesso!',
                'user'    => $user,
                'avatar_html' => $avatarHtml
            ]);
            break;

        case 'logout':
            AuthService::logout();
            echo json_encode([
                'success' => true,
                'message' => 'Sessão encerrada com sucesso.'
            ]);
            break;

        case 'me':
            $user = AuthService::getLoggedUser();
            $avatarHtml = $user ? AvatarService::renderAvatar($user, 36) : '';

            echo json_encode([
                'success'     => true,
                'logged_in'   => (bool) $user,
                'user'        => $user,
                'avatar_html' => $avatarHtml
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ação inválida.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
