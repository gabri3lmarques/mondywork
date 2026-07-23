<?php

namespace App\Scrapers;

interface ScraperInterface
{
    /**
     * @return \App\DTO\VagaDTO[]
     */
    public function fetchJobs(): array;

    public function getName(): string;
}
