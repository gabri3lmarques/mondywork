<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class CategoriaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categorias ORDER BY nome_pt ASC");
        return $stmt->fetchAll();
    }

    public function findAllWithActiveCount(): array
    {
        $sql = "
            SELECT c.*, 
                (SELECT COUNT(*) 
                 FROM vaga_categorias vc 
                 JOIN vagas v ON v.id = vc.vaga_id 
                 WHERE vc.categoria_id = c.id AND v.status = 'ativa' AND v.is_nao_listada = 0) AS total_ativas
            FROM categorias c 
            ORDER BY c.nome_pt ASC
        ";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function attachCategoriesToVaga(int $vagaId, array $slugs): void
    {
        $stmtDel = $this->pdo->prepare("DELETE FROM vaga_categorias WHERE vaga_id = :id");
        $stmtDel->execute([':id' => $vagaId]);

        if (empty($slugs)) {
            $slugs = ['sem-categoria'];
        }

        $stmtIns = $this->pdo->prepare("INSERT INTO vaga_categorias (vaga_id, categoria_id) SELECT :vaga_id, id FROM categorias WHERE slug = :slug");
        foreach ($slugs as $slug) {
            $stmtIns->execute([':vaga_id' => $vagaId, ':slug' => $slug]);
        }
    }
}
