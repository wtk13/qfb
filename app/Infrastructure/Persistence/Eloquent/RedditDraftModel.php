<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RedditDraftModel extends Model
{
    protected $table = 'reddit_drafts';

    protected $fillable = [
        'reddit_thread_id',
        'reddit_subreddit_id',
        'type',
        'content_category',
        'title',
        'body',
        'status',
        'reddit_thing_id',
        'published_at',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'reddit_score',
        'reddit_num_comments',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(RedditThreadModel::class, 'reddit_thread_id');
    }

    public function subreddit(): BelongsTo
    {
        return $this->belongsTo(RedditSubredditModel::class, 'reddit_subreddit_id');
    }
}
