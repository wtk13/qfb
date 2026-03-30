<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedditSubredditModel extends Model
{
    protected $table = 'reddit_subreddits';

    protected $fillable = [
        'name',
        'tier',
        'max_posts_per_week',
        'max_comments_per_week',
        'rules_json',
        'keywords_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules_json' => 'array',
            'keywords_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function threads(): HasMany
    {
        return $this->hasMany(RedditThreadModel::class, 'reddit_subreddit_id');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(RedditDraftModel::class, 'reddit_subreddit_id');
    }
}
