<?php

declare(strict_types=1);

namespace App\Application\Command\Reddit;

use Domain\Reddit\Port\RedditApiInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\PhasePolicy;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;
use Illuminate\Support\Facades\Log;

class PublishApprovedDrafts
{
    public function __construct(
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditThreadRepositoryInterface $threadRepo,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private RedditApiInterface $redditApi,
    ) {}

    public function execute(): int
    {
        $accountCreatedAt = config('reddit.account_created_at');
        if (! $accountCreatedAt) {
            return 0;
        }

        $phasePolicy = new PhasePolicy(new \DateTimeImmutable($accountCreatedAt));
        if (! $phasePolicy->canDraft()) {
            return 0;
        }

        $isDryRun = config('reddit.dry_run');

        $drafts = $this->draftRepo->findByStatus(DraftStatus::Approved, 3);
        $publishedCount = 0;

        foreach ($drafts as $draft) {
            $subreddit = $this->subredditRepo->findById($draft->subredditId);
            if (! $subreddit) {
                continue;
            }

            $weekCounts = $this->draftRepo->countPublishedThisWeek($subreddit->id);
            $cadence = new SubredditCadencePolicy(
                $subreddit->maxPostsPerWeek,
                $subreddit->maxCommentsPerWeek,
                $weekCounts['posts'],
                $weekCounts['comments'],
            );

            if (! $cadence->canPublish($draft->type)) {
                continue;
            }

            try {
                if ($isDryRun) {
                    Log::info('Reddit dry run: would publish draft', ['draft_id' => $draft->id, 'type' => $draft->type->value]);

                    continue;
                }

                if ($draft->type === DraftType::Comment && $draft->threadId) {
                    $thread = $this->threadRepo->findById($draft->threadId);
                    if (! $thread) {
                        continue;
                    }
                    $thingId = $this->redditApi->submitComment($thread->redditId, $draft->body);
                } else {
                    $thingId = $this->redditApi->submitPost(
                        $subreddit->name,
                        $draft->title ?? '',
                        $draft->body,
                    );
                }

                $draft->markPublished($thingId);
                $this->draftRepo->save($draft);
                $publishedCount++;

                Log::info('Reddit draft published', ['draft_id' => $draft->id, 'thing_id' => $thingId]);

                if ($publishedCount < count($drafts)) {
                    sleep(rand(120, 300));
                }
            } catch (\Throwable $e) {
                $draft->markFailed();
                $this->draftRepo->save($draft);
                Log::error('Reddit publish failed', ['draft_id' => $draft->id, 'error' => $e->getMessage()]);
            }
        }

        return $publishedCount;
    }
}
