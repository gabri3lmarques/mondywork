<?php

namespace App\Controllers;

use App\Repositories\VagaRepository;

class ApiController
{
    private VagaRepository $vagaRepo;

    public function __construct(?VagaRepository $vagaRepo = null)
    {
        $this->vagaRepo = $vagaRepo ?? new VagaRepository();
    }

    public function getVagaByExternalId(string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $vaga = $this->vagaRepo->findByExternalId($id);

        if (!$vaga) {
            http_response_code(404);
            echo json_encode(['error' => 'Vaga não encontrada']);
            return;
        }

        echo json_encode(['success' => true, 'vaga' => $vaga->toArray()]);
    }
}
