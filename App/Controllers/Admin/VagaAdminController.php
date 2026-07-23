<?php

namespace App\Controllers\Admin;

use App\Repositories\VagaRepository;
use App\Services\VagaService;

class VagaAdminController
{
    private VagaRepository $vagaRepo;
    private VagaService $vagaService;

    public function __construct(
        ?VagaRepository $vagaRepo = null,
        ?VagaService $vagaService = null
    ) {
        $this->vagaRepo    = $vagaRepo ?? new VagaRepository();
        $this->vagaService = $vagaService ?? new VagaService();
    }

    public function toggleStatus(int $id): bool
    {
        return $this->vagaService->alternarStatus($id);
    }

    public function getActiveCount(string $origem = 'nacional'): int
    {
        return $this->vagaRepo->countActive($origem);
    }
}
