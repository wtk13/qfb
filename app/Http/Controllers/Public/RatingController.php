<?php

namespace App\Http\Controllers\Public;

use App\Application\Command\SubmitRating;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Domain\Feedback\Service\RatingRoutingService;
use Domain\Feedback\ValueObject\Source;
use Illuminate\Http\Request;

class RatingController
{
    public function __construct(
        private BusinessProfileRepositoryInterface $profileRepository,
        private ReviewRequestRepositoryInterface $reviewRequestRepository,
        private SubmitRating $submitRating,
    ) {}

    public function show(string $slug, string $token)
    {
        $profile = $this->profileRepository->findBySlug($slug);

        if (! $profile) {
            abort(404);
        }

        $reviewRequest = $this->reviewRequestRepository->findByToken($token);

        if ($reviewRequest) {
            if ($reviewRequest->businessProfileId !== $profile->id) {
                abort(404);
            }
            if ($reviewRequest->status->canTransitionTo(ReviewRequestStatus::Clicked)) {
                $reviewRequest->markAsClicked();
                $this->reviewRequestRepository->save($reviewRequest);
            }
        }

        return view('rate.show', [
            'profile' => $profile,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $slug, string $token)
    {
        $profile = $this->profileRepository->findBySlug($slug);

        if (! $profile) {
            abort(404);
        }

        $validated = $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        $reviewRequest = $this->reviewRequestRepository->findByToken($token);
        $source = $reviewRequest ? Source::Email : Source::QrCode;

        $result = $this->submitRating->execute(
            businessProfileId: $profile->id,
            score: (int) $validated['score'],
            reviewRequestId: $reviewRequest?->id,
            source: $source,
        );

        if ($result['route'] === RatingRoutingService::ROUTE_GOOGLE) {
            return view('rate.thank-you', [
                'profile' => $profile,
                'googleRedirect' => $profile->googleReviewLink?->value,
            ]);
        }

        return redirect()->route('rate.feedback', [
            'slug' => $slug,
            'token' => $token,
            'rating_id' => $result['rating']->id,
        ]);
    }
}
