<?php

namespace App\Listeners;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Models\User;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotifyOwnerOnNegativeFeedback implements ShouldQueue
{
    public function handle(NegativeFeedbackReceived $event): void
    {
        $business = BusinessProfileModel::find($event->businessProfileId);

        if (!$business) {
            return;
        }

        $owner = User::where('tenant_id', $business->tenant_id)->first();

        if (!$owner) {
            return;
        }

        $feedback = FeedbackModel::find($event->feedbackId);

        Mail::raw(
            __('feedback.notification_body', [
                'business' => $business->name,
                'comment' => $feedback?->comment ?? '',
            ]),
            function ($message) use ($owner, $business) {
                $message->to($owner->email)
                    ->subject(__('feedback.notification_subject', ['business' => $business->name]));
            }
        );
    }
}
