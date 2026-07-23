<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class BlogRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function findLatest(int $limit = 9, string $lang = 'pt'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT slug, title, excerpt, image, categoria, author, published_at 
            FROM blog_posts 
            WHERE status = 'publicado' AND lang = :lang 
            ORDER BY published_at DESC 
            LIMIT :lim
        ");
        $stmt->bindValue(':lang', $lang, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $result = $stmt->fetch();

        return $result ?: null;
    }
}
