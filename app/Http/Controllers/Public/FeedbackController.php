<?php

namespace App\Http\Controllers\Public;

use App\Application\Command\SubmitFeedback;
use App\Application\Query\GetFeedbackList;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Illuminate\Http\Request;

class FeedbackController
{
    public function __construct(
        private SubmitFeedback $submitFeedback,
        private GetFeedbackList $getFeedbackList,
        private BusinessProfileRepositoryInterface $profileRepository,
    ) {}

    public function showForm(string $slug, string $token, Request $request)
    {
        $profile = $this->profileRepository->findBySlug($slug);

        if (! $profile) {
            abort(404);
        }

        return view('rate.feedback', [
            'profile' => $profile,
            'token' => $token,
            'ratingId' => $request->query('rating_id'),
        ]);
    }

    public function store(Request $request, string $slug, string $token)
    {
        $profile = $this->profileRepository->findBySlug($slug);

        if (! $profile) {
            abort(404);
        }

        $validated = $request->validate([
            'rating_id' => 'required|string',
            'comment' => 'required|string|max:2000',
            'contact_email' => 'nullable|email',
        ]);

        $this->submitFeedback->execute(
            ratingId: $validated['rating_id'],
            comment: $validated['comment'],
            contactEmail: $validated['contact_email'] ?? null,
        );

        return view('rate.thank-you', [
            'profile' => $profile,
            'googleRedirect' => null,
        ]);
    }

    public function index(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (! $profile || ! $profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        $feedbackList = $this->getFeedbackList->execute($id);

        return view('feedback.index', [
            'profile' => $profile,
            'feedbackList' => $feedbackList,
        ]);
    }
}
