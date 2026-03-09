<?php

namespace App\Application\Command;

use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Feedback\Entity\Rating;
use Domain\Feedback\Event\RatingSubmitted;
use Domain\Feedback\Port\RatingRepositoryInterface;
use Domain\Feedback\Service\RatingRoutingService;
use Domain\Feedback\ValueObject\Score;
use Domain\Feedback\ValueObject\Source;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class SubmitRating
{
    public function __construct(
        private RatingRepositoryInterface $ratingRepository,
        private ReviewRequestRepositoryInterface $reviewRequestRepository,
        private RatingRoutingService $routingService,
        private Dispatcher $eventDispatcher,
    ) {}

    public function execute(
        string $businessProfileId,
        int $score,
        ?string $reviewRequestId = null,
        Source $source = Source::Email,
    ): array {
        $scoreVo = new Score($score);

        $rating = new Rating(
            id: (string) Str::uuid(),
            businessProfileId: $businessProfileId,
            reviewRequestId: $reviewRequestId,
            score: $scoreVo,
            source: $source,
        );

        $this->ratingRepository->save($rating);

        if ($reviewRequestId) {
            $reviewRequest = $this->reviewRequestRepository->findById($reviewRequestId);
            if ($reviewRequest) {
                try {
                    $reviewRequest->markAsRated();
                    $this->reviewRequestRepository->save($reviewRequest);
                } catch (\DomainException) {
                    // Already rated
                }
            }
        }

        $this->eventDispatcher->dispatch(new RatingSubmitted(
            ratingId: $rating->id,
            businessProfileId: $businessProfileId,
            score: $score,
        ));

        $route = $this->routingService->determineRoute($scoreVo);

        return [
            'rating' => $rating,
            'route' => $route,
        ];
    }
}
