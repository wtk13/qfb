<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private TenantModel $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantModel::create(['name' => 'Test Tenant', 'slug' => 'test-tenant-123', 'trial_ends_at' => now()->addDays(14)]);
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    public function test_can_create_business_profile(): void
    {
        $response = $this->actingAs($this->user)->post('/business-profiles', [
            'name' => 'My Business',
            'address' => '123 Main St',
            'google_review_link' => 'https://www.google.com/maps/place/test',
        ]);

        $response->assertRedirect(route('business-profiles.index'));

        $this->assertDatabaseHas('business_profiles', [
            'tenant_id' => $this->tenant->id,
            'name' => 'My Business',
        ]);
    }

    public function test_can_list_business_profiles(): void
    {
        BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Business 1',
            'slug' => 'business-1-abc',
        ]);

        $response = $this->actingAs($this->user)->get('/business-profiles');
        $response->assertStatus(200);
        $response->assertSee('Business 1');
    }

    public function test_tenant_isolation(): void
    {
        $otherTenant = TenantModel::create(['name' => 'Other', 'slug' => 'other-123']);
        BusinessProfileModel::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Business',
            'slug' => 'other-business-abc',
        ]);

        $response = $this->actingAs($this->user)->get('/business-profiles');
        $response->assertDontSee('Other Business');
    }

    public function test_cannot_access_other_tenant_profile(): void
    {
        $otherTenant = TenantModel::create(['name' => 'Other', 'slug' => 'other-456']);
        $profile = BusinessProfileModel::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Business',
            'slug' => 'other-business-xyz',
        ]);

        $response = $this->actingAs($this->user)->get("/business-profiles/{$profile->id}");
        $response->assertStatus(403);
    }

    public function test_can_update_business_profile(): void
    {
        $profile = BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Old Name',
            'slug' => 'old-name-abc',
        ]);

        $response = $this->actingAs($this->user)->put("/business-profiles/{$profile->id}", [
            'name' => 'New Name',
        ]);

        $response->assertRedirect(route('business-profiles.show', $profile->id));
        $this->assertDatabaseHas('business_profiles', ['id' => $profile->id, 'name' => 'New Name']);
    }

    public function test_can_delete_business_profile(): void
    {
        $profile = BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'To Delete',
            'slug' => 'to-delete-abc',
        ]);

        $response = $this->actingAs($this->user)->delete("/business-profiles/{$profile->id}");

        $response->assertRedirect(route('business-profiles.index'));
        $this->assertDatabaseMissing('business_profiles', ['id' => $profile->id]);
    }
}
