<?php

declare(strict_types=1);

namespace Domain\Identity\Port;

use Domain\Identity\Entity\Tenant;
use Domain\Shared\ValueObject\TenantId;

interface TenantRepositoryInterface
{
    public function findById(TenantId $id): ?Tenant;

    public function findBySlug(string $slug): ?Tenant;

    public function save(Tenant $tenant): void;
}
