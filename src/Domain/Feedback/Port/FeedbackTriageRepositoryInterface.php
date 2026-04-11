<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\FeedbackTriage;

interface FeedbackTriageRepositoryInterface
{
    public function save(FeedbackTriage $triage): void;

    public function findByFeedbackId(string $feedbackId): ?FeedbackTriage;
}
