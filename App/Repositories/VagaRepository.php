<?php

namespace App\Repositories;

use App\Core\Database;
use App\DTO\VagaDTO;
use PDO;

class VagaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    public function findById(int $id): ?VagaDTO
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vagas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        return $data ? VagaDTO::fromArray($data) : null;
    }

    public function findByExternalId(string $externalId): ?VagaDTO
    {
        $stmt = $this->pdo->prepare("SELECT * FROM vagas WHERE vaga_id_externo = :id LIMIT 1");
        $stmt->execute([':id' => $externalId]);
        $data = $stmt->fetch();

        return $data ? VagaDTO::fromArray($data) : null;
    }

    public function countActive(string $origem = 'nacional'): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM vagas WHERE status = 'ativa' AND origem = :origem");
        $stmt->execute([':origem' => $origem]);
        return (int)$stmt->fetchColumn();
    }

    public function findActive(string $origem = 'nacional', int $limit = 24, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM vagas 
            WHERE status = 'ativa' AND origem = :origem 
            ORDER BY publicado_em DESC, id DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':origem', $origem);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $vagas = [];
        foreach ($stmt->fetchAll() as $row) {
            $vagas[] = VagaDTO::fromArray($row);
        }
        return $vagas;
    }

    public function upsert(VagaDTO $vaga): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO vagas (vaga_id_externo, titulo, empresa, localizacao, modelo_trabalho, url_vaga, descricao, resumo, publicado_em, status, origem)
            VALUES (:id, :titulo, :empresa, :local, :modelo, :url, :desc, :resumo, :publicado, :status, :origem)
            ON DUPLICATE KEY UPDATE 
                data_coleta = CURRENT_TIMESTAMP, 
                modelo_trabalho = VALUES(modelo_trabalho), 
                publicado_em = VALUES(publicado_em), 
                origem = VALUES(origem)
        ");

        $stmt->execute([
            ':id'        => $vaga->vagaIdExterno,
            ':titulo'    => $vaga->titulo,
            ':empresa'   => $vaga->empresa,
            ':local'     => $vaga->localizacao,
            ':modelo'    => $vaga->modeloTrabalho,
            ':url'       => $vaga->urlVaga,
            ':desc'      => $vaga->descricao,
            ':resumo'    => $vaga->resumo,
            ':publicado' => $vaga->publicadoEm,
            ':status'    => $vaga->status,
            ':origem'    => $vaga->origem,
        ]);

        $vagaId = (int)$this->pdo->lastInsertId();
        if ($vagaId === 0) {
            $stmtId = $this->pdo->prepare("SELECT id FROM vagas WHERE vaga_id_externo = :id LIMIT 1");
            $stmtId->execute([':id' => $vaga->vagaIdExterno]);
            $vagaId = (int)$stmtId->fetchColumn();
        }

        return $vagaId;
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE vagas SET status = IF(status = 'ativa', 'inativa', 'ativa') WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare("UPDATE vagas SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
