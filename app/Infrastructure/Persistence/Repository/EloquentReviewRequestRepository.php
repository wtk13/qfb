<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Campaign\Entity\ReviewRequest;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Domain\Campaign\ValueObject\ReviewToken;
use Domain\Shared\ValueObject\Email;

class EloquentReviewRequestRepository implements ReviewRequestRepositoryInterface
{
    public function findById(string $id): ?ReviewRequest
    {
        $model = ReviewRequestModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByToken(string $token): ?ReviewRequest
    {
        $model = ReviewRequestModel::where('token', $token)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByBusinessProfileId(string $businessProfileId): array
    {
        return ReviewRequestModel::where('business_profile_id', $businessProfileId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (ReviewRequestModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(ReviewRequest $reviewRequest): void
    {
        ReviewRequestModel::updateOrCreate(
            ['id' => $reviewRequest->id],
            [
                'id' => $reviewRequest->id,
                'business_profile_id' => $reviewRequest->businessProfileId,
                'recipient_email' => $reviewRequest->recipientEmail->value,
                'status' => $reviewRequest->status->value,
                'token' => $reviewRequest->token->value,
                'sent_at' => $reviewRequest->sentAt,
            ]
        );
    }

    public function countByBusinessProfileId(string $businessProfileId): int
    {
        return ReviewRequestModel::where('business_profile_id', $businessProfileId)->count();
    }

    private function toDomain(ReviewRequestModel $model): ReviewRequest
    {
        return new ReviewRequest(
            id: $model->id,
            businessProfileId: $model->business_profile_id,
            recipientEmail: new Email($model->recipient_email),
            status: ReviewRequestStatus::from($model->status),
            token: new ReviewToken($model->token),
            sentAt: $model->sent_at ? \DateTimeImmutable::createFromMutable($model->sent_at) : null,
        );
    }
}
