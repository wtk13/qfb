<?php

declare(strict_types=1);

namespace Domain\Campaign\Port;

use Domain\Campaign\Entity\ReviewRequest;

interface ReviewRequestRepositoryInterface
{
    public function findById(string $id): ?ReviewRequest;

    public function findByToken(string $token): ?ReviewRequest;

    public function findByBusinessProfileId(string $businessProfileId): array;

    public function save(ReviewRequest $reviewRequest): void;

    public function countByBusinessProfileId(string $businessProfileId): int;
}
