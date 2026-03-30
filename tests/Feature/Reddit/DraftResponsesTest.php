<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\DraftResponses;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Port\AiDrafterInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftResponsesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_drafts_for_new_threads(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d')]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_test1',
            'title' => 'How to get more reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        $mockDrafter = $this->mock(AiDrafterInterface::class);
        $mockDrafter->shouldReceive('generateDraft')
            ->once()
            ->andReturn('Here is my helpful response about getting reviews...');

        $command = app(DraftResponses::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_drafts', [
            'status' => 'pending',
            'type' => 'comment',
            'body' => 'Here is my helpful response about getting reviews...',
        ]);
        $this->assertDatabaseHas('reddit_threads', [
            'reddit_id' => 't3_test1',
            'status' => 'drafted',
        ]);
    }

    public function test_skips_during_lurk_phase(): void
    {
        config(['reddit.account_created_at' => now()->subDays(5)->format('Y-m-d')]);

        $command = app(DraftResponses::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }
}
