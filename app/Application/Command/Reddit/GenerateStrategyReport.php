<?php

namespace App\Application\Command\Reddit;

use Domain\Reddit\Entity\RedditStrategyReport;
use Domain\Reddit\Port\AiStrategistInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\PhasePolicy;

class GenerateStrategyReport
{
    public function __construct(
        private RedditDraftRepositoryInterface $draftRepo,
        private RedditStrategyReportRepositoryInterface $reportRepo,
        private AiStrategistInterface $aiStrategist,
    ) {}

    public function execute(): void
    {
        $periodEnd = new \DateTimeImmutable;
        $periodStart = new \DateTimeImmutable('-7 days');

        $contentRatio = $this->draftRepo->countByContentCategoryBetween($periodStart, $periodEnd);

        $published = $this->draftRepo->findByStatus(DraftStatus::Published->value, 100);
        $thisWeekPublished = array_filter($published, fn ($d) => $d->createdAt >= $periodStart);

        $topPerforming = array_filter($thisWeekPublished, fn ($d) => $d->redditScore !== null);
        usort($topPerforming, fn ($a, $b) => ($b->redditScore ?? 0) <=> ($a->redditScore ?? 0));
        $topPerforming = array_slice($topPerforming, 0, 5);

        $accountCreatedAt = config('reddit.account_created_at');
        $phasePolicy = $accountCreatedAt ? new PhasePolicy(new \DateTimeImmutable($accountCreatedAt)) : null;

        $metricsContext = [
            'period' => $periodStart->format('Y-m-d').' to '.$periodEnd->format('Y-m-d'),
            'content_ratio' => $contentRatio,
            'published_count' => count($thisWeekPublished),
            'phase' => $phasePolicy?->currentPhase()->value ?? 'unknown',
            'account_age_days' => $phasePolicy?->accountAgeDays() ?? 0,
            'top_performing' => array_map(fn ($d) => [
                'body_preview' => substr($d->body, 0, 100),
                'score' => $d->redditScore,
                'comments' => $d->redditNumComments,
                'category' => $d->contentCategory->value,
            ], $topPerforming),
        ];

        $reportContent = $this->aiStrategist->analyzeWeeklyMetrics($metricsContext);

        $report = new RedditStrategyReport(
            id: 0,
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            reportJson: $reportContent,
            recommendationsJson: $reportContent['recommendations'] ?? [],
            contentRatioJson: $contentRatio,
            topPerformingJson: array_map(fn ($d) => [
                'id' => $d->id,
                'body_preview' => substr($d->body, 0, 200),
                'score' => $d->redditScore,
                'category' => $d->contentCategory->value,
            ], $topPerforming),
            createdAt: new \DateTimeImmutable,
        );

        $this->reportRepo->save($report);
    }
}
