<?php

declare(strict_types=1);

namespace Domain\Business\ValueObject;

final readonly class GoogleReviewLink
{
    public readonly string $value;

    public function __construct(string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid URL: {$value}");
        }

        if (! str_contains($value, 'google.com') && ! str_contains($value, 'google.')) {
            throw new \InvalidArgumentException('Google review link must be a Google URL.');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
