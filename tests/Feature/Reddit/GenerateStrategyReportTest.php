<?php

namespace Tests\Feature\Reddit;

use App\Application\Command\Reddit\GenerateStrategyReport;
use App\Infrastructure\Persistence\Eloquent\RedditDraftModel;
use App\Infrastructure\Persistence\Eloquent\RedditSubredditModel;
use App\Infrastructure\Persistence\Eloquent\RedditThreadModel;
use Domain\Reddit\Port\AiStrategistInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateStrategyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_strategy_report(): void
    {
        config(['reddit.account_created_at' => now()->subDays(20)->format('Y-m-d')]);

        $sub = RedditSubredditModel::create([
            'name' => 'smallbusiness',
            'tier' => 1,
        ]);

        $thread = RedditThreadModel::create([
            'reddit_subreddit_id' => $sub->id,
            'reddit_id' => 't3_strat1',
            'title' => 'How to get reviews',
            'author' => 'testuser',
            'url' => 'https://reddit.com/r/test/1',
            'thread_type' => 'how_to_get_reviews',
            'status' => 'drafted',
            'discovered_at' => now(),
        ]);

        RedditDraftModel::create([
            'reddit_thread_id' => $thread->id,
            'reddit_subreddit_id' => $sub->id,
            'type' => 'comment',
            'content_category' => 'value',
            'body' => 'Helpful comment',
            'status' => 'published',
            'published_at' => now(),
            'reddit_score' => 12,
            'reddit_num_comments' => 3,
        ]);

        $mockStrategist = $this->mock(AiStrategistInterface::class);
        $mockStrategist->shouldReceive('analyzeWeeklyMetrics')
            ->once()
            ->andReturn([
                'summary' => 'Good week overall.',
                'working_well' => 'Value content performing well.',
                'needs_improvement' => 'More discussion posts needed.',
                'recommendations' => ['Focus on r/smallbusiness', 'Increase discussion ratio'],
                'phase_assessment' => 'On track for Full phase.',
            ]);

        $command = app(GenerateStrategyReport::class);
        $command->execute();

        $this->assertDatabaseHas('reddit_strategy_reports', [
            'id' => 1,
        ]);

        $report = \App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel::first();
        $this->assertSame('Good week overall.', $report->report_json['summary']);
        $this->assertCount(2, $report->report_json['recommendations']);
    }

    public function test_generates_report_with_no_data(): void
    {
        config(['reddit.account_created_at' => now()->subDays(5)->format('Y-m-d')]);

        $mockStrategist = $this->mock(AiStrategistInterface::class);
        $mockStrategist->shouldReceive('analyzeWeeklyMetrics')
            ->once()
            ->andReturn(['summary' => 'No activity this week.']);

        $command = app(GenerateStrategyReport::class);
        $command->execute();

        $this->assertDatabaseCount('reddit_strategy_reports', 1);
    }
}
