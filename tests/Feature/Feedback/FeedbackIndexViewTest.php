<?php

namespace Tests\Feature\Feedback;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackModel;
use App\Infrastructure\Persistence\Eloquent\FeedbackTriageModel;
use App\Infrastructure\Persistence\Eloquent\RatingModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackIndexViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t', 'trial_ends_at' => now()->addDays(14)]);
        $this->user = User::factory()->create(['tenant_id' => $tenant->id]);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz',
        ]);
    }

    public function test_shows_triage_card_when_triage_exists(): void
    {
        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 2,
            'source' => 'email',
        ]);
        $feedback = FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Staff was rude.',
        ]);
        FeedbackTriageModel::create([
            'feedback_id' => $feedback->id,
            'category' => 'staff',
            'urgency' => 'high',
            'suggested_response' => 'We sincerely apologize.',
            'raw_llm_response' => '{}',
            'model_used' => 'test-model',
            'triaged_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->get("/business-profiles/{$this->profile->id}/feedback");

        $response->assertStatus(200);
        $response->assertSee('Staff was rude.');
        $response->assertSee('Staff');
        $response->assertSee('High');
        $response->assertSee('We sincerely apologize.');
    }

    public function test_shows_plain_card_when_no_triage(): void
    {
        $rating = RatingModel::create([
            'business_profile_id' => $this->profile->id,
            'score' => 3,
            'source' => 'email',
        ]);
        FeedbackModel::create([
            'rating_id' => $rating->id,
            'comment' => 'Could be better.',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/business-profiles/{$this->profile->id}/feedback");

        $response->assertStatus(200);
        $response->assertSee('Could be better.');
        $response->assertDontSee('Suggested response');
    }
}
