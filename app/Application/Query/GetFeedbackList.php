<?php

namespace App\Application\Query;

use Domain\Feedback\Port\FeedbackRepositoryInterface;

class GetFeedbackList
{
    public function __construct(
        private FeedbackRepositoryInterface $repository,
    ) {}

    public function execute(string $businessProfileId): array
    {
        return $this->repository->findByBusinessProfileId($businessProfileId);
    }
}
