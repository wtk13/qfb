<?php

declare(strict_types=1);

namespace Domain\Feedback\Port;

use Domain\Feedback\Entity\FeedbackTriage;

interface FeedbackTriageServiceInterface
{
    public function triage(string $comment, int $score): FeedbackTriage;
}
