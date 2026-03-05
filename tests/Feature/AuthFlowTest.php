<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_tenant(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->tenant_id);

        $tenant = TenantModel::find($user->tenant_id);
        $this->assertNotNull($tenant);
        $this->assertSame('Test User', $tenant->name);
    }

    public function test_login_and_access_dashboard(): void
    {
        $tenant = TenantModel::create(['name' => 'Test', 'slug' => 'test-123']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }
}
