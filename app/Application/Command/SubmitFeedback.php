<?php

namespace App\Application\Command;

use Domain\Feedback\Entity\Feedback;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\RatingRepositoryInterface;
use Domain\Shared\ValueObject\Email;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class SubmitFeedback
{
    public function __construct(
        private FeedbackRepositoryInterface $feedbackRepository,
        private RatingRepositoryInterface $ratingRepository,
        private Dispatcher $eventDispatcher,
    ) {}

    public function execute(
        string $ratingId,
        string $comment,
        ?string $contactEmail = null,
    ): Feedback {
        $rating = $this->ratingRepository->findById($ratingId);

        if (! $rating) {
            throw new \RuntimeException('Rating not found.');
        }

        $feedback = new Feedback(
            id: (string) Str::uuid(),
            ratingId: $ratingId,
            comment: $comment,
            contactEmail: $contactEmail ? new Email($contactEmail) : null,
        );

        $this->feedbackRepository->save($feedback);

        $this->eventDispatcher->dispatch(new NegativeFeedbackReceived(
            feedbackId: $feedback->id,
            ratingId: $ratingId,
            businessProfileId: $rating->businessProfileId,
        ));

        return $feedback;
    }
}
