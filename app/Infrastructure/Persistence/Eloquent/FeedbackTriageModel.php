<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackTriageModel extends Model
{
    use HasUuids;

    protected $table = 'feedback_triages';

    protected $fillable = [
        'id',
        'feedback_id',
        'category',
        'urgency',
        'suggested_response',
        'raw_llm_response',
        'model_used',
        'triaged_at',
    ];

    protected function casts(): array
    {
        return [
            'triaged_at' => 'datetime',
        ];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(FeedbackModel::class, 'feedback_id');
    }
}
