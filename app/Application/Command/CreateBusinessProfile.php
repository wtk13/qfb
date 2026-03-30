<?php

namespace App\Application\Command;

use Domain\Business\Entity\BusinessProfile;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Business\ValueObject\GoogleReviewLink;
use Domain\Shared\ValueObject\TenantId;
use Illuminate\Support\Str;

class CreateBusinessProfile
{
    public function __construct(
        private BusinessProfileRepositoryInterface $repository,
    ) {}

    public function execute(
        TenantId $tenantId,
        string $name,
        ?string $address = null,
        ?string $googleReviewLink = null,
        ?string $logoPath = null,
        string $locale = 'en',
    ): BusinessProfile {
        $profile = new BusinessProfile(
            id: (string) Str::uuid(),
            tenantId: $tenantId,
            name: $name,
            address: $address,
            googleReviewLink: $googleReviewLink ? new GoogleReviewLink($googleReviewLink) : null,
            logoPath: $logoPath,
            locale: $locale,
            slug: Str::slug($name).'-'.Str::random(6),
        );

        $this->repository->save($profile);

        return $profile;
    }
}
