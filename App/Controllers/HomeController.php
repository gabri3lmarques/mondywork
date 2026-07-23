<?php

namespace App\Controllers;

use App\Repositories\BlogRepository;
use App\Repositories\VagaRepository;

class HomeController
{
    private VagaRepository $vagaRepo;
    private BlogRepository $blogRepo;

    public function __construct(
        ?VagaRepository $vagaRepo = null,
        ?BlogRepository $blogRepo = null
    ) {
        $this->vagaRepo = $vagaRepo ?? new VagaRepository();
        $this->blogRepo = $blogRepo ?? new BlogRepository();
    }

    public function index(string $origem = 'nacional', int $page = 1, int $limit = 24): array
    {
        $offset     = max(0, ($page - 1) * $limit);
        $totalVagas = $this->vagaRepo->countActive($origem);
        $vagas      = $this->vagaRepo->findActive($origem, $limit, $offset);
        $blogPosts  = $this->blogRepo->findLatest(9, 'pt');

        return [
            'vagas'       => $vagas,
            'total_vagas' => $totalVagas,
            'total_pages' => (int)ceil($totalVagas / $limit),
            'current_page'=> $page,
            'blog_posts'  => $blogPosts,
        ];
    }
}
