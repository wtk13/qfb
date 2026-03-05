<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantModel extends Model
{
    use HasUuids;

    protected $table = 'tenants';

    protected $fillable = ['name', 'slug'];

    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class, 'tenant_id');
    }

    public function businessProfiles(): HasMany
    {
        return $this->hasMany(BusinessProfileModel::class, 'tenant_id');
    }
}
