<?php

namespace App\Repositories;

use App\Core\Database;
use Exception;
use PDO;

class InteracaoRepository
{
    private PDO $pdo;

    public const VALID_REACTIONS = ['like', 'dislike', 'love', 'angry'];

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?: Database::getConnection();
    }

    /**
     * Retorna o resumo de reações de uma vaga e a reação do usuário (se logado)
     */
    public function getReactionsSummary(int $vagaId, ?int $usuarioId = null): array
    {
        $stmt = $this->pdo->prepare("
            SELECT tipo, COUNT(*) as total 
            FROM vaga_reacoes 
            WHERE vaga_id = :vaga_id 
            GROUP BY tipo
        ");
        $stmt->execute([':vaga_id' => $vagaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'like'    => 0,
            'dislike' => 0,
            'love'    => 0,
            'angry'   => 0,
            'total'   => 0
        ];

        foreach ($rows as $r) {
            $tipo = $r['tipo'];
            if (isset($counts[$tipo])) {
                $counts[$tipo] = (int) $r['total'];
                $counts['total'] += (int) $r['total'];
            }
        }

        $userReaction = null;
        if ($usuarioId) {
            $stmtUser = $this->pdo->prepare("
                SELECT tipo FROM vaga_reacoes 
                WHERE vaga_id = :vaga_id AND usuario_id = :usuario_id 
                LIMIT 1
            ");
            $stmtUser->execute([':vaga_id' => $vagaId, ':usuario_id' => $usuarioId]);
            $userReaction = $stmtUser->fetchColumn() ?: null;
        }

        return [
            'counts'        => $counts,
            'user_reaction' => $userReaction
        ];
    }

    /**
     * Alterna ou atualiza a reação de um usuário na vaga
     */
    public function toggleReaction(int $vagaId, int $usuarioId, string $tipo): array
    {
        if (!in_array($tipo, self::VALID_REACTIONS, true)) {
            throw new Exception('Tipo de reação inválido.');
        }

        // Verifica reação atual
        $stmtCheck = $this->pdo->prepare("
            SELECT id, tipo FROM vaga_reacoes 
            WHERE vaga_id = :vaga_id AND usuario_id = :usuario_id 
            LIMIT 1
        ");
        $stmtCheck->execute([':vaga_id' => $vagaId, ':usuario_id' => $usuarioId]);
        $current = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            if ($current['tipo'] === $tipo) {
                // Clique na mesma reação -> remove (toggle off)
                $stmtDel = $this->pdo->prepare("DELETE FROM vaga_reacoes WHERE id = :id");
                $stmtDel->execute([':id' => $current['id']]);
                $newUserReaction = null;
            } else {
                // Troca de reação
                $stmtUp = $this->pdo->prepare("UPDATE vaga_reacoes SET tipo = :tipo WHERE id = :id");
                $stmtUp->execute([':tipo' => $tipo, ':id' => $current['id']]);
                $newUserReaction = $tipo;
            }
        } else {
            // Nova reação
            $stmtIns = $this->pdo->prepare("
                INSERT INTO vaga_reacoes (vaga_id, usuario_id, tipo, created_at)
                VALUES (:vaga_id, :usuario_id, :tipo, NOW())
            ");
            $stmtIns->execute([
                ':vaga_id'    => $vagaId,
                ':usuario_id' => $usuarioId,
                ':tipo'       => $tipo
            ]);
            $newUserReaction = $tipo;
        }

        $summary = $this->getReactionsSummary($vagaId, $usuarioId);
        $summary['user_reaction'] = $newUserReaction;
        return $summary;
    }

    /**
     * Lista comentários de uma vaga
     */
    public function getComments(int $vagaId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                c.id,
                c.vaga_id,
                c.usuario_id,
                c.comentario,
                c.created_at,
                u.nome as usuario_nome,
                u.foto as usuario_foto
            FROM vaga_comentarios c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE c.vaga_id = :vaga_id
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([':vaga_id' => $vagaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona um comentário
     */
    public function addComment(int $vagaId, int $usuarioId, string $comentario): int
    {
        $comentario = trim($comentario);
        if (mb_strlen($comentario) < 2) {
            throw new Exception('O comentário deve ter pelo menos 2 caracteres.');
        }
        if (mb_strlen($comentario) > 1000) {
            throw new Exception('O comentário não pode ultrapassar 1000 caracteres.');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO vaga_comentarios (vaga_id, usuario_id, comentario, created_at)
            VALUES (:vaga_id, :usuario_id, :comentario, NOW())
        ");
        $stmt->execute([
            ':vaga_id'    => $vagaId,
            ':usuario_id' => $usuarioId,
            ':comentario' => $comentario
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Exclui um comentário (autor ou admin)
     */
    public function deleteComment(int $commentId, int $usuarioId, bool $isAdmin = false): bool
    {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("DELETE FROM vaga_comentarios WHERE id = :id");
            return $stmt->execute([':id' => $commentId]);
        }

        $stmt = $this->pdo->prepare("DELETE FROM vaga_comentarios WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->execute([':id' => $commentId, ':usuario_id' => $usuarioId]);
        return $stmt->rowCount() > 0;
    }
}
