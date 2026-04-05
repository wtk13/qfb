<?php

namespace Tests\Feature\Onboarding;

use App\Application\Query\GetOnboardingStatus;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Domain\Shared\ValueObject\TenantId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetOnboardingStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_tenant_has_all_steps_incomplete(): void
    {
        $tenant = TenantModel::create([
            'name' => 'New Tenant',
            'slug' => 'new-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $query = new GetOnboardingStatus;
        $result = $query->execute(new TenantId($tenant->id));

        $this->assertFalse($result['completed']);
        $this->assertCount(3, $result['steps']);
        $this->assertFalse($result['steps'][0]['done']);
        $this->assertFalse($result['steps'][1]['done']);
        $this->assertFalse($result['steps'][2]['done']);
    }

    public function test_tenant_with_business_profile_has_first_step_complete(): void
    {
        $tenant = TenantModel::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'My Business',
            'slug' => 'my-business-abc123',
        ]);

        $query = new GetOnboardingStatus;
        $result = $query->execute(new TenantId($tenant->id));

        $this->assertFalse($result['completed']);
        $this->assertTrue($result['steps'][0]['done']);
        $this->assertFalse($result['steps'][1]['done']);
        $this->assertFalse($result['steps'][2]['done']);
    }

    public function test_tenant_with_google_link_has_second_step_complete(): void
    {
        $tenant = TenantModel::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'My Business',
            'slug' => 'my-business-abc123',
            'google_review_link' => 'https://search.google.com/local/writereview?placeid=ABC123',
        ]);

        $query = new GetOnboardingStatus;
        $result = $query->execute(new TenantId($tenant->id));

        $this->assertTrue($result['steps'][0]['done']);
        $this->assertTrue($result['steps'][1]['done']);
        $this->assertFalse($result['steps'][2]['done']);
    }

    public function test_all_steps_complete_when_review_request_sent(): void
    {
        $tenant = TenantModel::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'My Business',
            'slug' => 'my-business-abc123',
            'google_review_link' => 'https://search.google.com/local/writereview?placeid=ABC123',
        ]);

        ReviewRequestModel::create([
            'business_profile_id' => $profile->id,
            'recipient_email' => 'customer@example.com',
            'token' => 'test-token-123',
        ]);

        $query = new GetOnboardingStatus;
        $result = $query->execute(new TenantId($tenant->id));

        $this->assertTrue($result['completed']);
        $this->assertTrue($result['steps'][0]['done']);
        $this->assertTrue($result['steps'][1]['done']);
        $this->assertTrue($result['steps'][2]['done']);
    }
}
