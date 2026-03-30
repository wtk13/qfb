<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Entity\RedditDraft;
use Domain\Reddit\Port\AiDrafterInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\ContentMixPolicy;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\PhasePolicy;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;
use Domain\Reddit\ValueObject\ThreadType;

class DraftResponses
{
    public function __construct(
        private RedditThreadRepositoryInterface $threadRepo,
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditSubredditRepositoryInterface $subredditRepo,
        private AiDrafterInterface $aiDrafter,
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

        $this->threadRepo->markStaleThreads(new \DateTimeImmutable('-24 hours'));

        $counts = $this->draftRepo->countByContentCategoryBetween(
            new \DateTimeImmutable('-30 days'),
            new \DateTimeImmutable,
        );
        $mixPolicy = new ContentMixPolicy($counts);

        $threads = $this->threadRepo->findNewThreads(10);
        $draftCount = 0;

        foreach ($threads as $thread) {
            $subreddit = $this->subredditRepo->findById($thread->subredditId);
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

            $draftType = DraftType::Comment;

            if (! $cadence->canPublish($draftType)) {
                continue;
            }

            $contentCategory = $mixPolicy->suggestCategory();

            if ($thread->threadType === ThreadType::ToolRecommendation && $mixPolicy->canGenerate(ContentCategory::Brand)) {
                $contentCategory = ContentCategory::Brand;
            }

            if (! $mixPolicy->canGenerate($contentCategory)) {
                $contentCategory = ContentCategory::Value;
            }

            $body = $this->aiDrafter->generateDraft(
                $thread,
                $subreddit->name,
                $subreddit->rulesJson,
                $contentCategory,
                $draftType,
            );

            $draft = new RedditDraft(
                id: 0,
                threadId: $thread->id,
                subredditId: $subreddit->id,
                type: $draftType,
                contentCategory: $contentCategory,
                title: null,
                body: $body,
                status: DraftStatus::Pending,
                redditThingId: null,
                publishedAt: null,
                approvedAt: null,
                rejectedAt: null,
                rejectionReason: null,
                redditScore: null,
                redditNumComments: null,
                createdAt: new \DateTimeImmutable,
            );

            $draft = $this->draftRepo->save($draft);
            $thread->markDrafted();
            $thread = $this->threadRepo->save($thread);

            $draftCount++;
        }

        return $draftCount;
    }
}
