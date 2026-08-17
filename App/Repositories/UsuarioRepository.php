<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UsuarioRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => mb_strtolower(trim($email), 'UTF-8')]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nome, email, foto, created_at FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function create(string $nome, string $email, string $senhaHash, ?string $foto = null): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, foto, created_at)
            VALUES (:nome, :email, :senha, :foto, NOW())
        ");
        $stmt->execute([
            ':nome'  => trim($nome),
            ':email' => mb_strtolower(trim($email), 'UTF-8'),
            ':senha' => $senhaHash,
            ':foto'  => $foto
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updatePhoto(int $id, string $foto): bool
    {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET foto = :foto WHERE id = :id");
        return $stmt->execute([':foto' => $foto, ':id' => $id]);
    }
}
