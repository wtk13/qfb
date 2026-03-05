<?php

declare(strict_types=1);

namespace Domain\Identity\Port;

use Domain\Identity\Entity\User;
use Domain\Shared\ValueObject\TenantId;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByTenantId(TenantId $tenantId): array;

    public function save(User $user): void;
}
