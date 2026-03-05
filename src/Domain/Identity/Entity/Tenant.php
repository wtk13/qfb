<?php

declare(strict_types=1);

namespace Domain\Identity\Entity;

use Domain\Shared\ValueObject\TenantId;

final class Tenant
{
    public function __construct(
        public readonly TenantId $id,
        public string $name,
        public string $slug,
    ) {}
}
