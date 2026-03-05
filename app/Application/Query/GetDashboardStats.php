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

        return [
            'total_businesses' => $profileIds->count(),
            'total_review_requests' => ReviewRequestModel::whereIn('business_profile_id', $profileIds)->count(),
            'total_ratings' => RatingModel::whereIn('business_profile_id', $profileIds)->count(),
            'average_score' => RatingModel::whereIn('business_profile_id', $profileIds)->avg('score'),
            'total_feedback' => FeedbackModel::whereHas('rating', function ($q) use ($profileIds) {
                $q->whereIn('business_profile_id', $profileIds);
            })->count(),
            'positive_ratings' => RatingModel::whereIn('business_profile_id', $profileIds)
                ->where('score', '>=', 4)->count(),
            'negative_ratings' => RatingModel::whereIn('business_profile_id', $profileIds)
                ->where('score', '<=', 3)->count(),
        ];
    }
}
