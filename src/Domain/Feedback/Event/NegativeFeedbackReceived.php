<?php

declare(strict_types=1);

namespace Domain\Feedback\Event;

use Domain\Shared\Event\DomainEventInterface;

final readonly class NegativeFeedbackReceived implements DomainEventInterface
{
    public function __construct(
        public string $feedbackId,
        public string $ratingId,
        public string $businessProfileId,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {}

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
