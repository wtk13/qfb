<?php

declare(strict_types=1);

namespace Domain\Feedback\ValueObject;

final readonly class Score
{
    public const int POSITIVE_THRESHOLD = 4;

    public function __construct(public int $value)
    {
        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException("Score must be between 1 and 5, got {$value}.");
        }
    }

    public function isPositive(): bool
    {
        return $this->value >= self::POSITIVE_THRESHOLD;
    }

    public function isNegative(): bool
    {
        return $this->value < self::POSITIVE_THRESHOLD;
    }
}
