<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ThreadStatus;
use Domain\Reddit\ValueObject\ThreadType;

class EloquentRedditThreadRepository implements RedditThreadRepositoryInterface
{
    public function findById(int $id): ?RedditThread
    {
        $model = RedditThreadModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByRedditId(string $redditId): ?RedditThread
    {
        $model = RedditThreadModel::where('reddit_id', $redditId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findNewThreads(int $limit = 10): array
    {
        return RedditThreadModel::where('status', ThreadStatus::New->value)
            ->orderBy('discovered_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (RedditThreadModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditThread $thread): RedditThread
    {
        $attributes = [
            'reddit_subreddit_id' => $thread->subredditId,
            'reddit_id' => $thread->redditId,
            'title' => $thread->title,
            'body' => $thread->body,
            'author' => $thread->author,
            'url' => $thread->url,
            'score' => $thread->score,
            'num_comments' => $thread->numComments,
            'thread_type' => $thread->threadType->value,
            'status' => $thread->status->value,
            'discovered_at' => $thread->discoveredAt,
        ];

        $model = $thread->id > 0
            ? RedditThreadModel::updateOrCreate(['id' => $thread->id], $attributes)
            : RedditThreadModel::updateOrCreate(['reddit_id' => $thread->redditId], $attributes);

        return $this->toDomain($model);
    }

    public function markStaleThreads(\DateTimeImmutable $olderThan): int
    {
        return RedditThreadModel::where('status', ThreadStatus::New->value)
            ->where('discovered_at', '<', $olderThan->format('Y-m-d H:i:s'))
            ->update(['status' => ThreadStatus::Stale->value]);
    }

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int
    {
        return RedditThreadModel::whereIn('status', array_map(fn ($s) => $s->value, $statuses))
            ->where('created_at', '<', $date->format('Y-m-d H:i:s'))
            ->delete();
    }

    private function toDomain(RedditThreadModel $model): RedditThread
    {
        return new RedditThread(
            id: $model->id,
            subredditId: $model->reddit_subreddit_id,
            redditId: $model->reddit_id,
            title: $model->title,
            body: $model->body,
            author: $model->author,
            url: $model->url,
            score: $model->score,
            numComments: $model->num_comments,
            threadType: ThreadType::from($model->thread_type),
            status: ThreadStatus::from($model->status),
            discoveredAt: \DateTimeImmutable::createFromInterface($model->discovered_at),
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
