<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingFlowTest extends TestCase
{
    use RefreshDatabase;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-t']);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Business',
            'slug' => 'test-business',
            'google_review_link' => 'https://www.google.com/maps/place/test',
        ]);
    }

    public function test_can_view_rating_page(): void
    {
        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $this->profile->id,
            'recipient_email' => 'customer@example.com',
            'status' => 'sent',
            'token' => 'test-token-123',
        ]);

        $response = $this->get("/rate/{$this->profile->slug}/{$reviewRequest->token}");
        $response->assertStatus(200);
        $response->assertSee('Test Business');
    }

    public function test_positive_rating_shows_google_redirect(): void
    {
        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $this->profile->id,
            'recipient_email' => 'customer@example.com',
            'status' => 'clicked',
            'token' => 'test-token-positive',
        ]);

        $response = $this->post("/rate/{$this->profile->slug}/{$reviewRequest->token}", [
            'score' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertSee('google.com');

        $this->assertDatabaseHas('ratings', [
            'business_profile_id' => $this->profile->id,
            'score' => 5,
        ]);
    }

    public function test_negative_rating_redirects_to_feedback(): void
    {
        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $this->profile->id,
            'recipient_email' => 'customer@example.com',
            'status' => 'clicked',
            'token' => 'test-token-negative',
        ]);

        $response = $this->post("/rate/{$this->profile->slug}/{$reviewRequest->token}", [
            'score' => 2,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('ratings', [
            'business_profile_id' => $this->profile->id,
            'score' => 2,
        ]);
    }

    public function test_can_submit_feedback(): void
    {
        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $this->profile->id,
            'recipient_email' => 'customer@example.com',
            'status' => 'clicked',
            'token' => 'test-token-feedback',
        ]);

        // First submit rating
        $this->post("/rate/{$this->profile->slug}/{$reviewRequest->token}", ['score' => 2]);

        $rating = \App\Infrastructure\Persistence\Eloquent\RatingModel::first();

        // Then submit feedback
        $response = $this->post("/rate/{$this->profile->slug}/{$reviewRequest->token}/feedback", [
            'rating_id' => $rating->id,
            'comment' => 'Service was slow.',
            'contact_email' => 'customer@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('feedback', [
            'rating_id' => $rating->id,
            'comment' => 'Service was slow.',
        ]);
    }
}
