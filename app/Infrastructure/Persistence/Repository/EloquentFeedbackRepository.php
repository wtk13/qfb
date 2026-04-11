<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use Domain\Feedback\Entity\Feedback;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Shared\ValueObject\Email;

class EloquentFeedbackRepository implements FeedbackRepositoryInterface
{
    public function findById(string $id): ?Feedback
    {
        $model = FeedbackModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByRatingId(string $ratingId): ?Feedback
    {
        $model = FeedbackModel::where('rating_id', $ratingId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function findByBusinessProfileId(string $businessProfileId): array
    {
        return FeedbackModel::whereHas('rating', function ($q) use ($businessProfileId) {
            $q->where('business_profile_id', $businessProfileId);
        })
            ->with(['rating', 'triage'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (FeedbackModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(Feedback $feedback): void
    {
        FeedbackModel::updateOrCreate(
            ['id' => $feedback->id],
            [
                'id' => $feedback->id,
                'rating_id' => $feedback->ratingId,
                'comment' => $feedback->comment,
                'contact_email' => $feedback->contactEmail?->value,
            ]
        );
    }

    public function countByBusinessProfileId(string $businessProfileId): int
    {
        return FeedbackModel::whereHas('rating', function ($q) use ($businessProfileId) {
            $q->where('business_profile_id', $businessProfileId);
        })->count();
    }

    private function toDomain(FeedbackModel $model): Feedback
    {
        $triage = null;

        if ($model->relationLoaded('triage') && $model->triage) {
            $triage = new \Domain\Feedback\Entity\FeedbackTriage(
                id: $model->triage->id,
                feedbackId: $model->triage->feedback_id,
                category: \Domain\Feedback\ValueObject\TriageCategory::from($model->triage->category),
                urgency: \Domain\Feedback\ValueObject\TriageUrgency::from($model->triage->urgency),
                suggestedResponse: $model->triage->suggested_response,
                rawLlmResponse: $model->triage->raw_llm_response,
                modelUsed: $model->triage->model_used,
                triagedAt: new \DateTimeImmutable($model->triage->triaged_at->toDateTimeString()),
            );
        }

        return new Feedback(
            id: $model->id,
            ratingId: $model->rating_id,
            comment: $model->comment,
            contactEmail: $model->contact_email ? new Email($model->contact_email) : null,
            score: $model->relationLoaded('rating') ? (int) $model->rating?->score : null,
            triage: $triage,
        );
    }
}
