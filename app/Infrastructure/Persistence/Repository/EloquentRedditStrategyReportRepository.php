<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RedditStrategyReportModel;
use Domain\Reddit\Entity\RedditStrategyReport;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;

class EloquentRedditStrategyReportRepository implements RedditStrategyReportRepositoryInterface
{
    public function findById(int $id): ?RedditStrategyReport
    {
        $model = RedditStrategyReportModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findLatest(): ?RedditStrategyReport
    {
        $model = RedditStrategyReportModel::orderByDesc('period_end')->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return RedditStrategyReportModel::orderByDesc('period_end')
            ->get()
            ->map(fn (RedditStrategyReportModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(RedditStrategyReport $report): void
    {
        $key = $report->id > 0 ? ['id' => $report->id] : ['period_start' => $report->periodStart->format('Y-m-d'), 'period_end' => $report->periodEnd->format('Y-m-d')];
        RedditStrategyReportModel::updateOrCreate(
            $key,
            [
                'period_start' => $report->periodStart->format('Y-m-d'),
                'period_end' => $report->periodEnd->format('Y-m-d'),
                'report_json' => $report->reportJson,
                'recommendations_json' => $report->recommendationsJson,
                'content_ratio_json' => $report->contentRatioJson,
                'top_performing_json' => $report->topPerformingJson,
            ]
        );
    }

    private function toDomain(RedditStrategyReportModel $model): RedditStrategyReport
    {
        return new RedditStrategyReport(
            id: $model->id,
            periodStart: \DateTimeImmutable::createFromInterface($model->period_start),
            periodEnd: \DateTimeImmutable::createFromInterface($model->period_end),
            reportJson: $model->report_json,
            recommendationsJson: $model->recommendations_json,
            contentRatioJson: $model->content_ratio_json,
            topPerformingJson: $model->top_performing_json,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
