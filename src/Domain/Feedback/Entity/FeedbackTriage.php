<?php

declare(strict_types=1);

namespace Domain\Feedback\Entity;

use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;

final class FeedbackTriage
{
    public function __construct(
        public readonly string $id,
        public readonly string $feedbackId,
        public readonly TriageCategory $category,
        public readonly TriageUrgency $urgency,
        public readonly string $suggestedResponse,
        public readonly string $rawLlmResponse,
        public readonly string $modelUsed,
        public readonly \DateTimeImmutable $triagedAt,
    ) {}
}
