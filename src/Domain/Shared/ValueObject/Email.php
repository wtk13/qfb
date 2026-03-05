<?php

declare(strict_types=1);

namespace Domain\Shared\ValueObject;

final readonly class Email
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $filtered = filter_var($value, FILTER_VALIDATE_EMAIL);

        if ($filtered === false) {
            throw new \InvalidArgumentException("Invalid email address: {$value}");
        }

        $this->value = $filtered;
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
