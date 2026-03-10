<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class TenantModel extends Model
{
    use HasUuids, Billable;

    protected $table = 'tenants';

    protected $fillable = ['id', 'name', 'slug', 'trial_ends_at'];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function getForeignKey(): string
    {
        return 'tenant_id';
    }

    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'tenant_id');
    }

    public function businessProfiles(): HasMany
    {
        return $this->hasMany(BusinessProfileModel::class, 'tenant_id');
    }
}
