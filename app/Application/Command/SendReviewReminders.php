<?php

namespace App\Application\Command;

use App\Jobs\SendReviewRequestJob;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;

class SendReviewReminders
{
    public function __construct(
        private ReviewRequestRepositoryInterface $repository,
    ) {}

    public function execute(\DateTimeImmutable $now): int
    {
        $sent = 0;

        foreach ($this->repository->findAwaitingReminder($now->modify('-14 days')) as $reviewRequest) {
            if (! $reviewRequest->needsReminder($now)) {
                continue;
            }

            $reviewRequest->markReminderSent();
            $this->repository->save($reviewRequest);
            SendReviewRequestJob::dispatch($reviewRequest->id, isReminder: true);
            $sent++;
        }

        return $sent;
    }
}
