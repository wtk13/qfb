<?php

namespace App\Http\Controllers\Webhook;

use App\Application\Command\SendReviewRequest;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Billing\Port\SubscriptionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lets other tools (Zapier "Webhooks by Zapier", Make, a booking system) send a review
 * request when a job is done: POST {email} to the profile's secret URL.
 */
class ReviewRequestWebhookController extends Controller
{
    private const DONT_ASK_AGAIN_DAYS = 30;

    public function __construct(
        private SendReviewRequest $sendReviewRequest,
        private SubscriptionServiceInterface $subscriptionService,
    ) {}

    public function __invoke(Request $request, string $token): JsonResponse
    {
        $profile = BusinessProfileModel::where('webhook_token', $token)->first();

        if (! $profile) {
            return response()->json(['error' => 'Unknown webhook URL.'], 404);
        }

        if (! $this->subscriptionService->isActive($profile->tenant_id)) {
            return response()->json(['error' => 'The QuickFeedback subscription for this business is not active.'], 402);
        }

        $email = strtolower(trim((string) $request->input('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => 'A valid "email" field is required.'], 422);
        }

        // Repeat customers and retried webhooks shouldn't get the same ask again.
        $askedRecently = ReviewRequestModel::where('business_profile_id', $profile->id)
            ->whereRaw('lower(recipient_email) = ?', [$email])
            ->where('created_at', '>', now()->subDays(self::DONT_ASK_AGAIN_DAYS))
            ->exists();

        if ($askedRecently) {
            return response()->json(['status' => 'skipped', 'reason' => 'Asked this customer in the last 30 days.']);
        }

        $reviewRequest = $this->sendReviewRequest->execute($profile->id, $email);

        return response()->json(['status' => 'queued', 'id' => $reviewRequest->id], 202);
    }
}
