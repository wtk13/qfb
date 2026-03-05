<?php

namespace App\Jobs;

use App\Infrastructure\Mail\ReviewRequestMail;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Campaign\ValueObject\ReviewRequestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $reviewRequestId,
    ) {}

    public function handle(): void
    {
        $model = ReviewRequestModel::findOrFail($this->reviewRequestId);
        $business = BusinessProfileModel::findOrFail($model->business_profile_id);

        $ratingUrl = route('rate.show', ['slug' => $business->slug, 'token' => $model->token]);

        Mail::to($model->recipient_email)->send(
            new ReviewRequestMail(
                businessName: $business->name,
                ratingUrl: $ratingUrl,
                logoPath: $business->logo_path,
            )
        );

        $model->update([
            'status' => ReviewRequestStatus::Sent->value,
            'sent_at' => now(),
        ]);
    }
}
