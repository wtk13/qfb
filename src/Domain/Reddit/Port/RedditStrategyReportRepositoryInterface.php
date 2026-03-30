<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditStrategyReport;

interface RedditStrategyReportRepositoryInterface
{
    public function findById(int $id): ?RedditStrategyReport;

    public function findLatest(): ?RedditStrategyReport;

    /** @return RedditStrategyReport[] */
    public function findAll(): array;

    public function save(RedditStrategyReport $report): void;
}
