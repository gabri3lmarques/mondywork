<?php

namespace App\Services;

use App\DTO\VagaDTO;
use App\Repositories\CategoriaRepository;
use App\Repositories\VagaRepository;

class VagaService
{
    private VagaRepository $vagaRepo;
    private CategoriaRepository $categoriaRepo;

    public function __construct(
        ?VagaRepository $vagaRepo = null,
        ?CategoriaRepository $categoriaRepo = null
    ) {
        $this->vagaRepo      = $vagaRepo ?? new VagaRepository();
        $this->categoriaRepo = $categoriaRepo ?? new CategoriaRepository();
    }

    public function processarVaga(VagaDTO $vaga): int
    {
        $vagaId = $this->vagaRepo->upsert($vaga);

        if ($vagaId > 0) {
            $tags = CategorizacaoService::classificar($vaga->titulo);
            $slugs = array_map(fn($tag) => CategorizacaoService::categoriaSlug($tag), $tags);
            $this->categoriaRepo->attachCategoriesToVaga($vagaId, $slugs);
        }

        return $vagaId;
    }

    public function alternarStatus(int $id): bool
    {
        return $this->vagaRepo->toggleStatus($id);
    }
}
