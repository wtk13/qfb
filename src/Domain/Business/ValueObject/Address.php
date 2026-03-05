<?php

declare(strict_types=1);

namespace Domain\Business\ValueObject;

final readonly class Address
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zip,
        public string $country,
    ) {}

    public function __toString(): string
    {
        return "{$this->street}, {$this->city}, {$this->zip}, {$this->country}";
    }
}
