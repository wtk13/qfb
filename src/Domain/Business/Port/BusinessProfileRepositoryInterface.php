<?php

declare(strict_types=1);

namespace Domain\Business\Port;

use Domain\Business\Entity\BusinessProfile;
use Domain\Shared\ValueObject\TenantId;

interface BusinessProfileRepositoryInterface
{
    public function findById(string $id): ?BusinessProfile;

    public function findBySlug(string $slug): ?BusinessProfile;

    public function findByTenantId(TenantId $tenantId): array;

    public function save(BusinessProfile $profile): void;

    public function delete(string $id): void;
}
