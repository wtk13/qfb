<?php

namespace Tests\Feature\Auth;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    private function mockGoogleUser(string $id, string $name, string $email): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getName')->andReturn($name);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_redirect_sends_user_to_google(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google');

        $response->assertRedirect();
    }

    public function test_new_google_user_creates_tenant_and_user(): void
    {
        $this->mockGoogleUser('google-123', 'Jane Doe', 'jane@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->tenant_id);
        $this->assertNotNull($user->email_verified_at);

        $tenant = TenantModel::find($user->tenant_id);
        $this->assertNotNull($tenant);
        $this->assertNotNull($tenant->trial_ends_at);
    }

    public function test_existing_user_with_google_id_logs_in(): void
    {
        $tenant = TenantModel::create([
            'name' => 'Existing Tenant',
            'slug' => 'existing-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'google_id' => 'google-123',
            'password' => null,
            'tenant_id' => $tenant->id,
        ]);

        $this->mockGoogleUser('google-123', 'Jane Doe', 'jane@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_existing_email_user_links_google_account(): void
    {
        $tenant = TenantModel::create([
            'name' => 'Existing Tenant',
            'slug' => 'existing-tenant-abc123',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        $this->mockGoogleUser('google-456', 'Jane Doe', 'jane@example.com');

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google-456', $user->fresh()->google_id);
        $this->assertSame(1, User::count());
        $this->assertSame(1, TenantModel::count());
    }

    public function test_google_auth_failure_redirects_to_login_with_error(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('OAuth failed'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }
}
