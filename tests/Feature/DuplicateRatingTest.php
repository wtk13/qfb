<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitting_rating_twice_does_not_error(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-dup']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz-dup',
            'google_review_link' => 'https://www.google.com/maps/place/test',
        ]);

        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $profile->id,
            'recipient_email' => 'dup@example.com',
            'status' => 'clicked',
            'token' => 'dup-token',
        ]);

        // First rating
        $response = $this->post("/rate/{$profile->slug}/{$reviewRequest->token}", ['score' => 5]);
        $response->assertStatus(200);

        // Second rating — should not throw
        $response = $this->post("/rate/{$profile->slug}/{$reviewRequest->token}", ['score' => 4]);
        $response->assertStatus(200);

        $this->assertDatabaseCount('ratings', 2);
    }

    public function test_viewing_rating_page_twice_does_not_error(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-view']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test Biz',
            'slug' => 'test-biz-view',
        ]);

        $reviewRequest = ReviewRequestModel::create([
            'business_profile_id' => $profile->id,
            'recipient_email' => 'view@example.com',
            'status' => 'sent',
            'token' => 'view-token',
        ]);

        // First view — marks as clicked
        $this->get("/rate/{$profile->slug}/{$reviewRequest->token}")->assertStatus(200);

        // Second view — already clicked, should not error
        $this->get("/rate/{$profile->slug}/{$reviewRequest->token}")->assertStatus(200);
    }

    public function test_qr_code_rating_works_without_review_request(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-qr']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'QR Biz',
            'slug' => 'qr-biz',
            'google_review_link' => 'https://www.google.com/maps/place/test',
        ]);

        $response = $this->get("/rate/{$profile->slug}/qr");
        $response->assertStatus(200);

        $response = $this->post("/rate/{$profile->slug}/qr", ['score' => 5]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('ratings', [
            'business_profile_id' => $profile->id,
            'score' => 5,
            'source' => 'qr_code',
        ]);
    }
}
