<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Domain\Feedback\Event\FeedbackTriaged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotifyOwnerOnNegativeFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private TenantModel $tenant;
    private BusinessProfileModel $profile;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_sends_enriched_email_when_triage_exists(): void
    {
        Mail::fake();

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Staff was rude.',
        ]);
        $triage = FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We sincerely apologize for this experience.',
            'raw_llm_response' => '{}',
            'model_used' => 'claude-haiku-4-5-20251001',
            'triaged_at' => now(),
        ]);

        $event = new FeedbackTriaged(
            feedbackId: $feedback->id,
            triageId: $triage->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\NotifyOwnerOnNegativeFeedback::class);
        $listener->handle($event);

        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo($this->owner->email);
        });
    }

    public function test_sends_fallback_email_when_triage_is_null(): void
    {
        Mail::fake();

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 1,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Awful.',
        ]);

        $event = new FeedbackTriaged(
            feedbackId: $feedback->id,
            triageId: null,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\NotifyOwnerOnNegativeFeedback::class);
        $listener->handle($event);

        Mail::assertSent(function (\Illuminate\Mail\Mailable $mail) {
            return $mail->hasTo($this->owner->email);
        });
    }
}
