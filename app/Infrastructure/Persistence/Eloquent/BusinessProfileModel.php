<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfileModel extends Model
{
    use HasUuids;

    protected $table = 'business_profiles';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'address',
        'google_review_link',
        'logo_path',
        'locale',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function reviewRequests(): HasMany
    {
        return $this->hasMany(ReviewRequestModel::class, 'business_profile_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(RatingModel::class, 'business_profile_id');
    }
}
