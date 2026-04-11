<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Event\FeedbackTriaged;
use Domain\Feedback\Event\NegativeFeedbackReceived;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TriageNegativeFeedbackListenerTest extends TestCase
{
    use RefreshDatabase;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_triages_feedback_and_dispatches_event(): void
    {
        Event::fake([FeedbackTriaged::class]);

        $mockTriage = new FeedbackTriage(
            id: 'triage-mock',
            feedbackId: '',
            category: TriageCategory::WaitTime,
            urgency: TriageUrgency::Medium,
            suggestedResponse: 'We are sorry about the wait.',
            rawLlmResponse: '{"category":"wait_time","urgency":"medium","suggested_response":"We are sorry about the wait."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: new \DateTimeImmutable,
        );

        $this->mock(FeedbackTriageServiceInterface::class)
            ->shouldReceive('triage')
            ->once()
            ->andReturn($mockTriage);

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Had to wait 45 minutes.',
        ]);

        $event = new NegativeFeedbackReceived(
            feedbackId: $feedback->id,
            ratingId: $rating->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\TriageNegativeFeedback::class);
        $listener->handle($event);

        $this->assertDatabaseHas('feedback_triages', [
            'feedback_id' => $feedback->id,
            'category' => 'wait_time',
            'urgency' => 'medium',
        ]);

        Event::assertDispatched(FeedbackTriaged::class, function ($e) use ($feedback) {
            return $e->feedbackId === $feedback->id && $e->triageId !== null;
        });
    }

    public function test_dispatches_event_with_null_triage_on_failure(): void
    {
        Event::fake([FeedbackTriaged::class]);

        $this->mock(FeedbackTriageServiceInterface::class)
            ->shouldReceive('triage')
            ->once()
            ->andThrow(new \RuntimeException('API error'));

        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 1,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Horrible.',
        ]);

        $event = new NegativeFeedbackReceived(
            feedbackId: $feedback->id,
            ratingId: $rating->id,
            businessProfileId: $this->profile->id,
        );

        $listener = $this->app->make(\App\Listeners\TriageNegativeFeedback::class);
        $listener->handle($event);

        $this->assertDatabaseMissing('feedback_triages', [
            'feedback_id' => $feedback->id,
        ]);

        Event::assertDispatched(FeedbackTriaged::class, function ($e) use ($feedback) {
            return $e->feedbackId === $feedback->id && $e->triageId === null;
        });
    }
}
