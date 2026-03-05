<?php

declare(strict_types=1);

namespace Domain\Shared\Event;

interface DomainEventInterface
{
    public function occurredAt(): \DateTimeImmutable;
}
