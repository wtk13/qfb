<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use Domain\Reddit\Entity\RedditDraft;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;

class EloquentRedditDraftRepository implements RedditDraftRepositoryInterface
{
    public function findById(int $id): ?RedditDraft
    {
        $model = RedditDraftModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByStatus(string $status, int $limit = 50): array
    {
        return RedditDraftModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (RedditDraftModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditDraft $draft): RedditDraft
    {
        $attributes = [
            'reddit_thread_id' => $draft->threadId,
            'reddit_subreddit_id' => $draft->subredditId,
            'type' => $draft->type->value,
            'content_category' => $draft->contentCategory->value,
            'title' => $draft->title,
            'body' => $draft->body,
            'status' => $draft->status->value,
            'reddit_thing_id' => $draft->redditThingId,
            'published_at' => $draft->publishedAt,
            'approved_at' => $draft->approvedAt,
            'rejected_at' => $draft->rejectedAt,
            'rejection_reason' => $draft->rejectionReason,
            'reddit_score' => $draft->redditScore,
            'reddit_num_comments' => $draft->redditNumComments,
        ];

        if ($draft->id > 0) {
            $model = RedditDraftModel::updateOrCreate(['id' => $draft->id], $attributes);
        } else {
            $model = RedditDraftModel::create($attributes);
        }

        return $this->toDomain($model);
    }

    public function countByContentCategoryBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $counts = RedditDraftModel::whereBetween('created_at', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
            ->whereIn('status', [DraftStatus::Pending->value, DraftStatus::Approved->value, DraftStatus::Published->value])
            ->selectRaw('content_category, COUNT(*) as count')
            ->groupBy('content_category')
            ->pluck('count', 'content_category')
            ->toArray();

        return [
            'value' => $counts[ContentCategory::Value->value] ?? 0,
            'discussion' => $counts[ContentCategory::Discussion->value] ?? 0,
            'brand' => $counts[ContentCategory::Brand->value] ?? 0,
        ];
    }

    public function countPublishedThisWeek(int $subredditId): array
    {
        $weekStart = now()->startOfWeek();

        $counts = RedditDraftModel::where('reddit_subreddit_id', $subredditId)
            ->where('status', DraftStatus::Published->value)
            ->where('published_at', '>=', $weekStart)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'posts' => $counts[DraftType::Post->value] ?? 0,
            'comments' => $counts[DraftType::Comment->value] ?? 0,
        ];
    }

    public function purgeOlderThan(\DateTimeImmutable $date, array $statuses): int
    {
        return RedditDraftModel::whereIn('status', array_map(fn ($s) => $s->value, $statuses))
            ->where('created_at', '<', $date->format('Y-m-d H:i:s'))
            ->delete();
    }

    private function toDomain(RedditDraftModel $model): RedditDraft
    {
        return new RedditDraft(
            id: $model->id,
            threadId: $model->reddit_thread_id,
            subredditId: $model->reddit_subreddit_id,
            type: DraftType::from($model->type),
            contentCategory: ContentCategory::from($model->content_category),
            title: $model->title,
            body: $model->body,
            status: DraftStatus::from($model->status),
            redditThingId: $model->reddit_thing_id,
            publishedAt: $model->published_at ? \DateTimeImmutable::createFromInterface($model->published_at) : null,
            approvedAt: $model->approved_at ? \DateTimeImmutable::createFromInterface($model->approved_at) : null,
            rejectedAt: $model->rejected_at ? \DateTimeImmutable::createFromInterface($model->rejected_at) : null,
            rejectionReason: $model->rejection_reason,
            redditScore: $model->reddit_score,
            redditNumComments: $model->reddit_num_comments,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
