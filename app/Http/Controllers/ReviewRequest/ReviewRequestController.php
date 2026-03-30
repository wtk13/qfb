<?php

namespace App\Http\Controllers\ReviewRequest;

use App\Application\Command\SendBulkReviewRequests;
use App\Application\Command\SendReviewRequest;
use App\Http\Controllers\Controller;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Illuminate\Http\Request;

class ReviewRequestController extends Controller
{
    public function __construct(
        private SendReviewRequest $sendReviewRequest,
        private SendBulkReviewRequests $sendBulkReviewRequests,
        private BusinessProfileRepositoryInterface $profileRepository,
    ) {}

    public function store(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (! $profile || ! $profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        $validated = $request->validate([
            'recipient_email' => 'required|email',
        ]);

        $this->sendReviewRequest->execute($id, $validated['recipient_email']);

        return redirect()->route('business-profiles.show', $id)
            ->with('success', __('campaign.request_sent'));
    }

    public function bulk(Request $request, string $id)
    {
        $profile = $this->profileRepository->findById($id);

        if (! $profile || ! $profile->tenantId->equals($request->get('tenant_id'))) {
            abort(403);
        }

        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $csvContent = file_get_contents($validated['csv_file']->getRealPath());
        $results = $this->sendBulkReviewRequests->executeFromCsv($id, $csvContent);

        return redirect()->route('business-profiles.show', $id)
            ->with('success', __('campaign.bulk_sent', ['count' => count($results)]));
    }
}
