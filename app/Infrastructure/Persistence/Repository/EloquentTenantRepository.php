<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Identity\Entity\Tenant;
use Domain\Identity\Port\TenantRepositoryInterface;
use Domain\Shared\ValueObject\TenantId;

class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(TenantId $id): ?Tenant
    {
        $model = TenantModel::find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = TenantModel::where('slug', $slug)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Tenant $tenant): void
    {
        TenantModel::updateOrCreate(
            ['id' => $tenant->id->value],
            [
                'id' => $tenant->id->value,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ]
        );
    }

    private function toDomain(TenantModel $model): Tenant
    {
        return new Tenant(
            id: new TenantId($model->id),
            name: $model->name,
            slug: $model->slug,
        );
    }
}
