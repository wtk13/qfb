<?php

namespace App\Application\Command;

use Domain\Business\Entity\BusinessProfile;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Business\ValueObject\GoogleReviewLink;

class UpdateBusinessProfile
{
    public function __construct(
        private BusinessProfileRepositoryInterface $repository,
    ) {}

    public function execute(
        string $id,
        string $name,
        ?string $address = null,
        ?string $googleReviewLink = null,
        ?string $logoPath = null,
        string $locale = 'en',
    ): BusinessProfile {
        $profile = $this->repository->findById($id);

        if (! $profile) {
            throw new \RuntimeException('Business profile not found.');
        }

        $profile->name = $name;
        $profile->address = $address;
        $profile->googleReviewLink = $googleReviewLink ? new GoogleReviewLink($googleReviewLink) : null;
        $profile->logoPath = $logoPath ?? $profile->logoPath;
        $profile->locale = $locale;

        $this->repository->save($profile);

        return $profile;
    }
}
