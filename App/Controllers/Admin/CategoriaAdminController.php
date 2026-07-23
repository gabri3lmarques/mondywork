<?php

namespace App\Controllers\Admin;

use App\Repositories\CategoriaRepository;

class CategoriaAdminController
{
    private CategoriaRepository $categoriaRepo;

    public function __construct(?CategoriaRepository $categoriaRepo = null)
    {
        $this->categoriaRepo = $categoriaRepo ?? new CategoriaRepository();
    }

    public function index(): array
    {
        return $this->categoriaRepo->findAllWithActiveCount();
    }
}
