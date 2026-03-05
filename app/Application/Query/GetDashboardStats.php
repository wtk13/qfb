<?php

namespace App\Application\Query;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Feedback\ValueObject\Score;
use Domain\Shared\ValueObject\TenantId;

class GetDashboardStats
{
    public function execute(TenantId $tenantId): array
    {
        $profileIds = BusinessProfileModel::where('tenant_id', $tenantId->value)
            ->pluck('id');

        $ratingStats = RatingModel::whereIn('business_profile_id', $profileIds)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(score) as avg_score')
            ->selectRaw('SUM(CASE WHEN score >= ? THEN 1 ELSE 0 END) as positive', [Score::POSITIVE_THRESHOLD])
            ->selectRaw('SUM(CASE WHEN score < ? THEN 1 ELSE 0 END) as negative', [Score::POSITIVE_THRESHOLD])
            ->first();

        return [
            'total_businesses' => $profileIds->count(),
            'total_review_requests' => ReviewRequestModel::whereIn('business_profile_id', $profileIds)->count(),
            'total_ratings' => (int) $ratingStats->total,
            'average_score' => $ratingStats->avg_score,
            'total_feedback' => FeedbackModel::whereHas('rating', function ($q) use ($profileIds) {
                $q->whereIn('business_profile_id', $profileIds);
            })->count(),
            'positive_ratings' => (int) $ratingStats->positive,
            'negative_ratings' => (int) $ratingStats->negative,
        ];
    }
}
