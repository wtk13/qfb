<?php

namespace Tests\Feature\Feedback;

use App\Application\Query\GetFeedbackList;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetFeedbackListWithTriageTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_feedback_with_triage_data(): void
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
            'comment' => 'Bad service.',
        ]);
        FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We apologize.',
            'raw_llm_response' => '{}',
            'model_used' => 'test-model',
            'triaged_at' => now(),
        ]);

        $query = $this->app->make(GetFeedbackList::class);
        $results = $query->execute($profile->id);

        $this->assertCount(1, $results);
        $this->assertNotNull($results[0]->triage);
        $this->assertSame('staff', $results[0]->triage->category->value);
        $this->assertSame('high', $results[0]->triage->urgency->value);
        $this->assertSame('We apologize.', $results[0]->triage->suggestedResponse);
    }

    public function test_returns_feedback_without_triage(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
        $rating = RatingModel::create([
            'business_profile_id' => $profile->id,
            'score' => 3,
            'source' => 'email',
        ]);
        FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Meh.',
        ]);

        $query = $this->app->make(GetFeedbackList::class);
        $results = $query->execute($profile->id);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->triage);
    }
}
