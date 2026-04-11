<?php

namespace App\Listeners;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Mail\NegativeFeedbackMail;
use App\Models\User;
use Domain\Feedback\Event\FeedbackTriaged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyOwnerOnNegativeFeedback implements ShouldQueue
{
    public function handle(FeedbackTriaged $event): void
    {
        $business = BusinessProfileModel::find($event->businessProfileId);

        if (! $business) {
            return;
        }

        $owner = User::where('tenant_id', $business->tenant_id)->first();

        if (! $owner) {
            return;
        }

        $feedback = FeedbackModel::find($event->feedbackId);
        $triage = $event->triageId ? FeedbackTriageModel::find($event->triageId) : null;

        Mail::to($owner->email)->send(new NegativeFeedbackMail(
            businessName: $business->name,
            comment: $feedback?->comment ?? '',
            triage: $triage,
        ));
    }
}
