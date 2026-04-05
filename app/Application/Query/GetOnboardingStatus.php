<?php

namespace App\Application\Query;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Shared\ValueObject\TenantId;

class GetOnboardingStatus
{
    public function execute(TenantId $tenantId): array
    {
        $profiles = BusinessProfileModel::where('tenant_id', $tenantId->value)->get();

        $hasProfile = $profiles->isNotEmpty();
        $hasGoogleLink = $profiles->contains(fn ($p) => $p->google_review_link !== null);
        $hasSentRequest = $hasProfile && ReviewRequestModel::whereIn(
            'business_profile_id',
            $profiles->pluck('id')
        )->exists();

        $firstProfileId = $profiles->first()?->id;

        $steps = [
            [
                'label' => 'Create your first business profile',
                'done' => $hasProfile,
                'url' => route('business-profiles.create'),
            ],
            [
                'label' => 'Add your Google review link',
                'done' => $hasGoogleLink,
                'url' => $firstProfileId
                    ? route('business-profiles.edit', $firstProfileId)
                    : route('business-profiles.create'),
            ],
            [
                'label' => 'Send your first review request',
                'done' => $hasSentRequest,
                'url' => $firstProfileId
                    ? route('business-profiles.show', $firstProfileId)
                    : route('business-profiles.create'),
            ],
        ];

        return [
            'completed' => $hasProfile && $hasGoogleLink && $hasSentRequest,
            'steps' => $steps,
        ];
    }
}
