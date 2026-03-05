<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\Feedback;

interface FeedbackRepositoryInterface
{
    public function findById(string $id): ?Feedback;

    public function findByRatingId(string $ratingId): ?Feedback;

    public function findByBusinessProfileId(string $businessProfileId): array;

    public function save(Feedback $feedback): void;

    public function countByBusinessProfileId(string $businessProfileId): int;
}
