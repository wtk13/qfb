<?php

namespace App\Application\Query;

use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Shared\ValueObject\TenantId;

class GetBusinessProfiles
{
    public function __construct(
        private BusinessProfileRepositoryInterface $repository,
    ) {}

    public function execute(TenantId $tenantId): array
    {
        return $this->repository->findByTenantId($tenantId);
    }
}
