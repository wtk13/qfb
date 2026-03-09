<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewRequestModel extends Model
{
    use HasUuids;

    protected $table = 'review_requests';

    protected $fillable = [
        'id',
        'business_profile_id',
        'recipient_email',
        'status',
        'token',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'business_profile_id');
    }
}
