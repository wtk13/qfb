<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackModel extends Model
{
    use HasUuids;

    protected $table = 'feedback';

    protected $fillable = [
        'id',
        'rating_id',
        'comment',
        'contact_email',
    ];

    public function rating(): BelongsTo
    {
        return $this->belongsTo(RatingModel::class, 'rating_id');
    }

    public function triage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FeedbackTriageModel::class, 'feedback_id');
    }
}
