<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Exception;

class AuthService
{
    private UsuarioRepository $userRepo;

    public function __construct(?UsuarioRepository $userRepo = null)
    {
        $this->userRepo = $userRepo ?: new UsuarioRepository();
    }

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getLoggedUser(): ?array
    {
        self::startSession();
        return $_SESSION['usuario'] ?? null;
    }

    public static function setLoggedUser(array $user): void
    {
        self::startSession();
        $_SESSION['usuario'] = [
            'id'    => (int) $user['id'],
            'nome'  => $user['nome'],
            'email' => $user['email'],
            'foto'  => $user['foto'] ?? null
        ];
    }

    public static function logout(): void
    {
        self::startSession();
        unset($_SESSION['usuario']);
    }

    public function register(string $nome, string $email, string $senha, ?array $fotoFile = null, string $lang = 'pt'): array
    {
        $nome = trim($nome);
        $email = mb_strtolower(trim($email), 'UTF-8');

        if (mb_strlen($nome) < 2) {
            throw new Exception($lang === 'en' ? 'Please enter your full name.' : 'Por favor, informe seu nome completo.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception($lang === 'en' ? 'Please enter a valid email address.' : 'Por favor, informe um e-mail válido.');
        }

        if (mb_strlen($senha) < 6) {
            throw new Exception($lang === 'en' ? 'Password must be at least 6 characters long.' : 'A senha deve ter pelo menos 6 caracteres.');
        }

        if ($this->userRepo->findByEmail($email)) {
            throw new Exception($lang === 'en' ? 'This email is already registered. Please sign in.' : 'Este e-mail já está cadastrado. Faça login para continuar.');
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $userId = $this->userRepo->create($nome, $email, $senhaHash, null);

        $fotoUrl = null;
        if ($fotoFile && !empty($fotoFile['tmp_name']) && is_uploaded_file($fotoFile['tmp_name'])) {
            try {
                $fotoUrl = AvatarService::processUpload($fotoFile, $userId);
                $this->userRepo->updatePhoto($userId, $fotoUrl);
            } catch (Exception $e) {
                // Se falhar o processamento da foto, o cadastro ainda ocorre e o fallback por letra é usado
            }
        }

        $user = [
            'id'    => $userId,
            'nome'  => $nome,
            'email' => $email,
            'foto'  => $fotoUrl
        ];

        self::setLoggedUser($user);
        return $user;
    }

    public function login(string $email, string $senha, string $lang = 'pt'): array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');

        if (empty($email) || empty($senha)) {
            throw new Exception($lang === 'en' ? 'Please enter your email and password.' : 'Preencha o e-mail e a senha.');
        }

        $user = $this->userRepo->findByEmail($email);
        if (!$user || !password_verify($senha, $user['senha'])) {
            throw new Exception($lang === 'en' ? 'Incorrect email or password.' : 'E-mail ou senha incorretos.');
        }

        $userData = [
            'id'    => (int) $user['id'],
            'nome'  => $user['nome'],
            'email' => $email,
            'foto'  => $user['foto'] ?? null
        ];

        self::setLoggedUser($userData);
        return $userData;
    }
}
