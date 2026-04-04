<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use Domain\Reddit\Port\RedditApiInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoutThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['reddit.client_id' => 'test_id', 'reddit.client_secret' => 'test_secret']);
    }

    public function test_scouts_and_saves_threads(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_abc123',
                    'title' => 'How to get more Google reviews?',
                    'selftext' => 'Looking for advice...',
                    'author' => 'testuser',
                    'url' => 'https://reddit.com/r/smallbusiness/abc123',
                    'score' => 5,
                    'num_comments' => 3,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reddit_threads', [
            'reddit_id' => 't3_abc123',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'new',
        ]);
    }

    public function test_skips_low_score_threads(): void
    {
        RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_low',
                    'title' => 'Some thread',
                    'selftext' => null,
                    'author' => 'user',
                    'url' => 'https://reddit.com/r/test/low',
                    'score' => 1,
                    'num_comments' => 0,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }

    public function test_skips_duplicate_threads(): void
    {
        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
            'keywords_json' => ['reviews'],
        ]);

        \App\Infrastructure\Persistence\Eloquent\RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_existing',
            'title' => 'Existing thread',
            'author' => 'user',
            'url' => 'https://reddit.com/r/test/existing',
            'thread_type' => 'general',
            'status' => 'new',
            'discovered_at' => now(),
        ]);

        $mockApi = $this->mock(RedditApiInterface::class);
        $mockApi->shouldReceive('searchSubreddit')
            ->andReturn([
                [
                    'id' => 't3_existing',
                    'title' => 'Existing thread',
                    'selftext' => null,
                    'author' => 'user',
                    'url' => 'https://reddit.com/r/test/existing',
                    'score' => 5,
                    'num_comments' => 2,
                    'created_utc' => time() - 3600,
                ],
            ]);

        $command = app(ScoutThreads::class);
        $count = $command->execute();

        $this->assertSame(0, $count);
    }
}
