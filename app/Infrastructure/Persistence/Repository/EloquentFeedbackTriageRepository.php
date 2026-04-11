<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;

class EloquentFeedbackTriageRepository implements FeedbackTriageRepositoryInterface
{
    public function save(FeedbackTriage $triage): void
    {
        FeedbackTriageModel::updateOrCreate(
            ['id' => $triage->id],
            [
                'id' => $triage->id,
                'feedback_id' => $triage->feedbackId,
                'category' => $triage->category->value,
                'urgency' => $triage->urgency->value,
                'suggested_response' => $triage->suggestedResponse,
                'raw_llm_response' => $triage->rawLlmResponse,
                'model_used' => $triage->modelUsed,
                'triaged_at' => $triage->triagedAt,
            ]
        );
    }

    public function findByFeedbackId(string $feedbackId): ?FeedbackTriage
    {
        $model = FeedbackTriageModel::where('feedback_id', $feedbackId)->first();

        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(FeedbackTriageModel $model): FeedbackTriage
    {
        return new FeedbackTriage(
            id: $model->id,
            feedbackId: $model->feedback_id,
            category: TriageCategory::from($model->category),
            urgency: TriageUrgency::from($model->urgency),
            suggestedResponse: $model->suggested_response,
            rawLlmResponse: $model->raw_llm_response,
            modelUsed: $model->model_used,
            triagedAt: new \DateTimeImmutable($model->triaged_at->toDateTimeString()),
        );
    }
}
