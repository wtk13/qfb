<?php

namespace App\Application\Query;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Shared\ValueObject\TenantId;

class GetDashboardStats
{
    public function execute(TenantId $tenantId): array
    {
        $profileIds = BusinessProfileModel::where('tenant_id', $tenantId->value)
            ->pluck('id');

        $scoreDistributionRaw = RatingModel::whereIn('business_profile_id', $profileIds)
            ->selectRaw('score, COUNT(*) as count')
            ->groupBy('score')
            ->orderBy('score')
            ->pluck('count', 'score');

        $scoreDistribution = collect(range(1, 5))->mapWithKeys(fn ($s) => [$s => (int) ($scoreDistributionRaw[$s] ?? 0)])->all();
        $totalRatings = array_sum($scoreDistribution);
        $positive = $scoreDistribution[4] + $scoreDistribution[5];
        $negative = $totalRatings - $positive;
        $averageScore = $totalRatings > 0
            ? collect($scoreDistribution)->reduce(fn ($sum, $count, $score) => $sum + $score * $count, 0) / $totalRatings
            : null;

        $ratingsOverTime = RatingModel::whereIn('business_profile_id', $profileIds)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->all();

        $ratingIds = $totalRatings > 0
            ? RatingModel::whereIn('business_profile_id', $profileIds)->pluck('id')
            : collect();

        return [
            'total_businesses' => $profileIds->count(),
            'total_review_requests' => ReviewRequestModel::whereIn('business_profile_id', $profileIds)->count(),
            'total_ratings' => $totalRatings,
            'average_score' => $averageScore,
            'total_feedback' => $ratingIds->isNotEmpty()
                ? FeedbackModel::whereIn('rating_id', $ratingIds)->count()
                : 0,
            'positive_ratings' => $positive,
            'negative_ratings' => $negative,
            'score_distribution' => $scoreDistribution,
            'ratings_over_time' => $ratingsOverTime,
        ];
    }
}
