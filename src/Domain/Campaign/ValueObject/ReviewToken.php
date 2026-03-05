<?php

declare(strict_types=1);

namespace Domain\Campaign\ValueObject;

final readonly class ReviewToken
{
    public readonly string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? bin2hex(random_bytes(32));
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
