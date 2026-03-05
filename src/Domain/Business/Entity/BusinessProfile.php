<?php

declare(strict_types=1);

namespace Domain\Business\Entity;

use Domain\Business\ValueObject\GoogleReviewLink;
use Domain\Shared\ValueObject\TenantId;

final class BusinessProfile
{
    public function __construct(
        public readonly string $id,
        public readonly TenantId $tenantId,
        public string $name,
        public ?string $address,
        public ?GoogleReviewLink $googleReviewLink,
        public ?string $logoPath,
        public string $locale = 'en',
        public ?string $slug = null,
    ) {}
}
