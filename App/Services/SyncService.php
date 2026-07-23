<?php

namespace App\Services;

use App\Scrapers\ScraperInterface;

class SyncService
{
    private VagaService $vagaService;
    /** @var ScraperInterface[] */
    private array $scrapers;

    public function __construct(?VagaService $vagaService = null, array $scrapers = [])
    {
        $this->vagaService = $vagaService ?? new VagaService();
        $this->scrapers    = $scrapers;
    }

    public function registerScraper(ScraperInterface $scraper): void
    {
        $this->scrapers[] = $scraper;
    }

    public function run(): array
    {
        $stats = [
            'total_coletadas' => 0,
            'por_fonte'       => [],
        ];

        foreach ($this->scrapers as $scraper) {
            $name = $scraper->getName();
            $vagas = $scraper->fetchJobs();
            $count = 0;

            foreach ($vagas as $vaga) {
                $this->vagaService->processarVaga($vaga);
                $count++;
            }

            $stats['por_fonte'][$name] = $count;
            $stats['total_coletadas'] += $count;
        }

        return $stats;
    }
}
