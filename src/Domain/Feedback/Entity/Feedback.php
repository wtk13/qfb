<?php

declare(strict_types=1);

namespace Domain\Feedback\Entity;

use Domain\Shared\ValueObject\Email;

final class Feedback
{
    public function __construct(
        public readonly string $id,
        public readonly string $ratingId,
        public readonly string $comment,
        public readonly ?Email $contactEmail = null,
        public readonly ?int $score = null,
    ) {}
}
