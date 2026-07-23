<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class NewsletterRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function subscribe(string $nome, string $email, string $area, string $origem = 'brasil'): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO newsletters (nome, email, area, origem) VALUES (:nome, :email, :area, :origem)");
        return $stmt->execute([
            ':nome'   => $nome,
            ':email'  => $email,
            ':area'   => $area,
            ':origem' => $origem,
        ]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM newsletters ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
}
