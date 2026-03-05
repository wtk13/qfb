<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RatingModel extends Model
{
    use HasUuids;

    protected $table = 'ratings';

    protected $fillable = [
        'business_profile_id',
        'review_request_id',
        'score',
        'source',
    ];

    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'business_profile_id');
    }

    public function reviewRequest(): BelongsTo
    {
        return $this->belongsTo(ReviewRequestModel::class, 'review_request_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(FeedbackModel::class, 'rating_id');
    }
}
