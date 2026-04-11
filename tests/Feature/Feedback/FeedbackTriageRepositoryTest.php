<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageRepositoryInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTriageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FeedbackTriageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(FeedbackTriageRepositoryInterface::class);
    }

    public function test_can_save_and_find_by_feedback_id(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
        $rating = RatingModel::create([
            'business_profile_id' => $profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Terrible service.',
        ]);

        $triageId = (string) \Illuminate\Support\Str::uuid();

        $triage = new FeedbackTriage(
            id: $triageId,
            feedbackId: $feedback->id,
            category: TriageCategory::Staff,
            urgency: TriageUrgency::High,
            suggestedResponse: 'We sincerely apologize for this experience.',
            rawLlmResponse: '{"category":"staff","urgency":"high","suggested_response":"We sincerely apologize for this experience."}',
            modelUsed: 'claude-haiku-4-5-20251001',
            triagedAt: new \DateTimeImmutable('2026-04-11 12:00:00'),
        );

        $this->repository->save($triage);

        $found = $this->repository->findByFeedbackId($feedback->id);

        $this->assertNotNull($found);
        $this->assertSame($triageId, $found->id);
        $this->assertSame($feedback->id, $found->feedbackId);
        $this->assertSame(TriageCategory::Staff, $found->category);
        $this->assertSame(TriageUrgency::High, $found->urgency);
        $this->assertSame('We sincerely apologize for this experience.', $found->suggestedResponse);
        $this->assertSame('claude-haiku-4-5-20251001', $found->modelUsed);
    }

    public function test_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->findByFeedbackId((string) \Illuminate\Support\Str::uuid()));
    }
}
