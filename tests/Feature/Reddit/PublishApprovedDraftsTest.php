<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\PublishApprovedDrafts;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishApprovedDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_approved_drafts(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d'), 'reddit.dry_run' => false]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);

        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Helpful comment here',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('submitComment')
            ->once()
            ->with('t3_test1', 'Helpful comment here')
            ->andReturn('t1_published123');

        $command = app(PublishApprovedDrafts::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'published',
            'reddit_thing_id' => 't1_published123',
        ]);
    }

    public function test_marks_draft_failed_on_api_error(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d'), 'reddit.dry_run' => false]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_fail',
            'title' => 'Thread',
            'author' => 'user',
            'url' => 'https://reddit.com/r/test/fail',
            'thread_type' => 'general',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);

        $draft = RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Will fail',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('submitComment')
            ->andThrow(new \RuntimeException('403 Forbidden'));

        $command = app(PublishApprovedDrafts::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'id' => $draft->id,
            'status' => 'failed',
        ]);
    }
}
