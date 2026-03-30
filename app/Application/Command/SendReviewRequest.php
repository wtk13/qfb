<?php

namespace App\Application\Command;

use App\Jobs\SendReviewRequestJob;
use Domain\Campaign\Entity\ReviewRequest;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Domain\Campaign\ValueObject\ReviewToken;
use Domain\Shared\ValueObject\Email;
use Illuminate\Support\Str;

class SendReviewRequest
{
    public function __construct(
        private ReviewRequestRepositoryInterface $repository,
    ) {}

    public function execute(string $businessProfileId, string $recipientEmail): ReviewRequest
    {
        $reviewRequest = new ReviewRequest(
            id: (string) Str::uuid(),
            businessProfileId: $businessProfileId,
            recipientEmail: new Email($recipientEmail),
            status: ReviewRequestStatus::Pending,
            token: new ReviewToken,
        );

        $this->repository->save($reviewRequest);

        SendReviewRequestJob::dispatch($reviewRequest->id);

        return $reviewRequest;
    }
}
