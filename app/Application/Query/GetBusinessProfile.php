<?php

namespace App\Application\Query;

use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\RatingRepositoryInterface;

class GetBusinessProfile
{
    public function __construct(
        private BusinessProfileRepositoryInterface $profileRepository,
        private RatingRepositoryInterface $ratingRepository,
        private ReviewRequestRepositoryInterface $reviewRequestRepository,
        private FeedbackRepositoryInterface $feedbackRepository,
    ) {}

    public function execute(string $id): ?array
    {
        $profile = $this->profileRepository->findById($id);

        if (! $profile) {
            return null;
        }

        return [
            'profile' => $profile,
            'stats' => [
                'total_ratings' => $this->ratingRepository->countByBusinessProfileId($id),
                'average_score' => $this->ratingRepository->averageScoreByBusinessProfileId($id),
                'total_review_requests' => $this->reviewRequestRepository->countByBusinessProfileId($id),
                'total_feedback' => $this->feedbackRepository->countByBusinessProfileId($id),
            ],
        ];
    }
}
