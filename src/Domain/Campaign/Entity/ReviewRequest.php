<?php

declare(strict_types=1);

namespace Domain\Campaign\Entity;

use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Domain\Campaign\ValueObject\ReviewToken;
use Domain\Shared\ValueObject\Email;

final class ReviewRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $businessProfileId,
        public readonly Email $recipientEmail,
        public ReviewRequestStatus $status,
        public readonly ReviewToken $token,
        public ?\DateTimeImmutable $sentAt = null,
    ) {}

    public function markAsSent(): void
    {
        $this->transitionTo(ReviewRequestStatus::Sent);
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markAsClicked(): void
    {
        $this->transitionTo(ReviewRequestStatus::Clicked);
    }

    public function markAsRated(): void
    {
        $this->transitionTo(ReviewRequestStatus::Rated);
    }

    private function transitionTo(ReviewRequestStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}."
            );
        }

        $this->status = $newStatus;
    }
}
