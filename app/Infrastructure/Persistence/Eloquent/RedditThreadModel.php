<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedditThreadModel extends Model
{
    protected $table = 'reddit_threads';

    protected $fillable = [
        'reddit_subreddit_id',
        'reddit_id',
        'title',
        'body',
        'author',
        'url',
        'score',
        'num_comments',
        'thread_type',
        'status',
        'discovered_at',
    ];

    protected function casts(): array
    {
        return [
            'discovered_at' => 'datetime',
        ];
    }

    public function subreddit(): BelongsTo
    {
        return $this->belongsTo(RedditSubredditModel::class, 'reddit_subreddit_id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(RedditDraftModel::class, 'reddit_thread_id');
    }
}
