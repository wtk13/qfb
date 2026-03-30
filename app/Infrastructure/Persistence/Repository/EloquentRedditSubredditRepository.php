<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Domain\Reddit\Entity\RedditSubreddit;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;

class EloquentRedditSubredditRepository implements RedditSubredditRepositoryInterface
{
    public function findById(int $id): ?RedditSubreddit
    {
        $model = RedditSubredditModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findActive(): array
    {
        return RedditSubredditModel::where('is_active', true)
            ->orderBy('tier')
            ->get()
            ->map(fn (RedditSubredditModel $m) => $this->toDomain($m))
            ->all();
    }

    public function findAll(): array
    {
        return RedditSubredditModel::orderBy('tier')
            ->orderBy('name')
            ->get()
            ->map(fn (RedditSubredditModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditSubreddit $subreddit): void
    {
        RedditSubredditModel::updateOrCreate(
            $subreddit->id > 0 ? ['id' => $subreddit->id] : ['name' => $subreddit->name],
            [
                'name' => $subreddit->name,
                'tier' => $subreddit->tier,
                'max_posts_per_week' => $subreddit->maxPostsPerWeek,
                'max_comments_per_week' => $subreddit->maxCommentsPerWeek,
                'rules_json' => $subreddit->rulesJson,
                'keywords_json' => $subreddit->keywordsJson,
                'is_active' => $subreddit->isActive,
            ]
        );
    }

    private function toDomain(RedditSubredditModel $model): RedditSubreddit
    {
        return new RedditSubreddit(
            id: $model->id,
            name: $model->name,
            tier: $model->tier,
            maxPostsPerWeek: $model->max_posts_per_week,
            maxCommentsPerWeek: $model->max_comments_per_week,
            rulesJson: $model->rules_json,
            keywordsJson: $model->keywords_json,
            isActive: $model->is_active,
        );
    }
}
