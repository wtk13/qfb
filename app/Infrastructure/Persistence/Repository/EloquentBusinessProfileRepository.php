<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use Domain\Business\Entity\BusinessProfile;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Business\ValueObject\GoogleReviewLink;
use Domain\Shared\ValueObject\TenantId;

class EloquentBusinessProfileRepository implements BusinessProfileRepositoryInterface
{
    public function findById(string $id): ?BusinessProfile
    {
        $model = BusinessProfileModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $slug): ?BusinessProfile
    {
        $model = BusinessProfileModel::where('slug', $slug)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByTenantId(TenantId $tenantId): array
    {
        return BusinessProfileModel::where('tenant_id', $tenantId->value)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (BusinessProfileModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(BusinessProfile $profile): void
    {
        BusinessProfileModel::updateOrCreate(
            ['id' => $profile->id],
            [
                'tenant_id' => $profile->tenantId->value,
                'name' => $profile->name,
                'slug' => $profile->slug,
                'address' => $profile->address,
                'google_review_link' => $profile->googleReviewLink?->value,
                'logo_path' => $profile->logoPath,
                'locale' => $profile->locale,
            ]
        );
    }

    public function delete(string $id): void
    {
        BusinessProfileModel::where('id', $id)->delete();
    }

    private function toDomain(BusinessProfileModel $model): BusinessProfile
    {
        return new BusinessProfile(
            id: $model->id,
            tenantId: new TenantId($model->tenant_id),
            name: $model->name,
            address: $model->address,
            googleReviewLink: $model->google_review_link ? new GoogleReviewLink($model->google_review_link) : null,
            logoPath: $model->logo_path,
            locale: $model->locale ?? 'en',
            slug: $model->slug,
        );
    }
}
