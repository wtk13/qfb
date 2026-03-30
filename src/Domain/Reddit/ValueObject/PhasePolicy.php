<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class PhasePolicy
{
    private const int LURK_DAYS = 14;
    private const int COMMENT_DAYS = 30;

    public function __construct(
        private \DateTimeImmutable $accountCreatedAt,
    ) {}

    public function currentPhase(\DateTimeImmutable $now = new \DateTimeImmutable()): Phase
    {
        $days = $this->accountAgeDays($now);

        if ($days <= self::LURK_DAYS) {
            return Phase::Lurk;
        }

        if ($days <= self::COMMENT_DAYS) {
            return Phase::Comment;
        }

        return Phase::Full;
    }

    public function accountAgeDays(\DateTimeImmutable $now = new \DateTimeImmutable()): int
    {
        return max(0, $this->accountCreatedAt->diff($now)->days);
    }

    public function canDraft(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        return $this->currentPhase($now) !== Phase::Lurk;
    }

    public function canPost(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        return $this->currentPhase($now) === Phase::Full;
    }
}
