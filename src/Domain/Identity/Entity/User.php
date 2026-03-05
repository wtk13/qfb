<?php

declare(strict_types=1);

namespace Domain\Identity\Entity;

use Domain\Shared\ValueObject\Email;
use Domain\Shared\ValueObject\TenantId;

final class User
{
    public function __construct(
        public readonly string $id,
        public readonly TenantId $tenantId,
        public string $name,
        public readonly Email $email,
    ) {}
}
