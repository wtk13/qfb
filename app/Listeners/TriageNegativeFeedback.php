<?php

namespace App\Listeners;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Event\FeedbackTriaged;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TriageNegativeFeedback implements ShouldQueue
{
    public function __construct(
        private FeedbackTriageServiceInterface $triageService,
        private FeedbackTriageRepositoryInterface $triageRepository,
        private FeedbackRepositoryInterface $feedbackRepository,
        private Dispatcher $eventDispatcher,
    ) {}

    public function handle(NegativeFeedbackReceived $event): void
    {
        $triageId = null;

        try {
            $feedback = $this->feedbackRepository->findById($event->feedbackId);

            if (! $feedback) {
                Log::warning('Feedback not found for triage', ['feedbackId' => $event->feedbackId]);
                return;
            }

            $result = $this->triageService->triage($feedback->comment, $feedback->score ?? 1);

            $triage = new FeedbackTriage(
                id: (string) Str::uuid(),
                feedbackId: $event->feedbackId,
                category: $result->category,
                urgency: $result->urgency,
                suggestedResponse: $result->suggestedResponse,
                rawLlmResponse: $result->rawLlmResponse,
                modelUsed: $result->modelUsed,
                triagedAt: $result->triagedAt,
            );

            $this->triageRepository->save($triage);
            $triageId = $triage->id;
        } catch (\Throwable $e) {
            Log::error('Feedback triage failed', [
                'feedbackId' => $event->feedbackId,
                'error' => $e->getMessage(),
            ]);
        }

        $this->eventDispatcher->dispatch(new FeedbackTriaged(
            feedbackId: $event->feedbackId,
            triageId: $triageId,
            businessProfileId: $event->businessProfileId,
        ));
    }
}
