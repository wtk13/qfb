<?php

declare(strict_types=1);

namespace Domain\Feedback\Entity;

use Domain\Feedback\ValueObject\Score;
use Domain\Feedback\ValueObject\Source;

final class Rating
{
    public function __construct(
        public readonly string $id,
        public readonly string $businessProfileId,
        public readonly ?string $reviewRequestId,
        public readonly Score $score,
        public readonly Source $source,
        public readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {}
}
