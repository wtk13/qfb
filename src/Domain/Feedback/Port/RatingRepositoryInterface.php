<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\Rating;

interface RatingRepositoryInterface
{
    public function findById(string $id): ?Rating;

    public function findByBusinessProfileId(string $businessProfileId): array;

    public function save(Rating $rating): void;

    public function countByBusinessProfileId(string $businessProfileId): int;

    public function averageScoreByBusinessProfileId(string $businessProfileId): ?float;
}
