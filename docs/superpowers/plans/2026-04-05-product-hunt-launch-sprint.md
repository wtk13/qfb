# Product Hunt Launch Sprint -- Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Polish QuickFeedback for a Product Hunt launch -- add Google OAuth registration/login and a post-signup onboarding checklist. (Negative feedback alerts already implemented.)

**Architecture:** Add Laravel Socialite for Google OAuth with a new controller that handles redirect/callback and creates tenant+user on first login. Add an Application query for onboarding status and a dashboard card that shows getting-started steps.

**Tech Stack:** Laravel Socialite, Google OAuth 2.0, Alpine.js

---

### Task 1: Install Socialite and configure Google provider

**Files:**
- Modify: `composer.json`
- Modify: `config/services.php`

- [ ] **Step 1: Install Socialite**

Run: `./vendor/bin/sail composer require laravel/socialite`

- [ ] **Step 2: Add Google config to `config/services.php`**

Add after the `google_places` block:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
],
```

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock config/services.php
git commit -m "chore: install Socialite and add Google OAuth config"
```

---

### Task 2: Migration -- add `google_id` to users, make `password` nullable

**Files:**
- Create: `database/migrations/2026_04_05_000001_add_google_id_to_users_table.php`
- Test: Run existing auth tests to verify no regression

- [ ] **Step 1: Create the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->string('password')->nullable(false)->change();
        });
    }
};
```

- [ ] **Step 2: Run migration**

Run: `./vendor/bin/sail artisan migrate`

- [ ] **Step 3: Update User model fillable**

In `app/Models/User.php`, add `'google_id'` to `$fillable`:

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'tenant_id',
    'google_id',
];
```

- [ ] **Step 4: Run existing auth tests**

Run: `./vendor/bin/sail artisan test --filter=Auth`
Expected: All 10+ tests pass (password is still set for normal registration).

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_05_000001_add_google_id_to_users_table.php app/Models/User.php
git commit -m "feat(auth): add google_id column and make password nullable"
```

---

### Task 3: Google OAuth controller

**Files:**
- Create: `app/Http/Controllers/Auth/GoogleAuthController.php`
- Create: `tests/Feature/Auth/GoogleAuthTest.php`

- [ ] **Step 1: Write the tests**

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/sail artisan test --filter=GoogleAuthTest`
Expected: FAIL -- `GoogleAuthController` class not found / routes not defined.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/Auth/GoogleAuthController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', __('auth.google_failed'));
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user, remember: true);

            return redirect()->route('dashboard');
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update(['google_id' => $googleUser->getId()]);
            Auth::login($user, remember: true);

            return redirect()->route('dashboard');
        }

        $tenant = TenantModel::create([
            'name' => $googleUser->getName(),
            'slug' => Str::slug($googleUser->getName()) . '-' . Str::random(6),
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => null,
            'tenant_id' => $tenant->id,
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }
}
```

- [ ] **Step 4: Add routes to `routes/auth.php`**

Add inside the existing `Route::middleware('guest')` group:

```php
Route::get('auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])
    ->name('auth.google');
Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');
```

- [ ] **Step 5: Add translation key**

In `lang/en/auth.php`, add:

```php
'google_failed' => 'Unable to sign in with Google. Please try again.',
```

In `lang/pl/auth.php`, add:

```php
'google_failed' => 'Nie udało się zalogować przez Google. Spróbuj ponownie.',
```

- [ ] **Step 6: Run tests**

Run: `./vendor/bin/sail artisan test --filter=GoogleAuthTest`
Expected: All 5 tests pass.

- [ ] **Step 7: Run all auth tests for regression**

Run: `./vendor/bin/sail artisan test --filter=Auth`
Expected: All pass.

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Auth/GoogleAuthController.php tests/Feature/Auth/GoogleAuthTest.php routes/auth.php lang/en/auth.php lang/pl/auth.php
git commit -m "feat(auth): add Google OAuth login and registration"
```

---

### Task 4: Google button on register and login pages

**Files:**
- Modify: `resources/views/auth/register.blade.php`
- Modify: `resources/views/auth/login.blade.php`

- [ ] **Step 1: Create a shared Google button partial**

Create `resources/views/auth/partials/google-button.blade.php`:

```blade
<a href="{{ route('auth.google') }}" class="w-full inline-flex items-center justify-center gap-3 px-4 py-3 bg-white border border-gray-300 rounded-md font-semibold text-sm text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
    <svg class="w-5 h-5" viewBox="0 0 24 24">
        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
    </svg>
    Continue with Google
</a>
```

- [ ] **Step 2: Update register page**

In `resources/views/auth/register.blade.php`, add the Google button and divider after the opening `<form>` tag's parent div. Replace the entire file content with:

```blade
<x-guest-layout>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Start your 14-day free trial</h1>
        <p class="mt-1 text-sm text-gray-500">No credit card required. Set up in under 5 minutes.</p>
    </div>

    @include('auth.partials.google-button')

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
        <div class="relative flex justify-center text-sm"><span class="bg-white px-4 text-gray-500">or</span></div>
    </div>

    @if(session('error'))
        <div class="mb-4 text-sm text-red-600">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                Try It Free for 14 Days
            </x-primary-button>
        </div>

        <ul class="mt-5 space-y-1.5 text-xs text-gray-500">
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Unlimited review requests
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Works with any Google Business Profile
            </li>
            <li class="flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Cancel anytime &mdash; no contracts
            </li>
        </ul>

        <div class="mt-4 text-center">
            <a class="text-sm text-gray-500 hover:text-gray-700" href="{{ route('login') }}">
                Already have an account? Log in
            </a>
        </div>
    </form>
</x-guest-layout>
```

- [ ] **Step 3: Update login page**

Replace `resources/views/auth/login.blade.php` with:

```blade
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @include('auth.partials.google-button')

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
        <div class="relative flex justify-center text-sm"><span class="bg-white px-4 text-gray-500">or</span></div>
    </div>

    @if(session('error'))
        <div class="mb-4 text-sm text-red-600">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
```

- [ ] **Step 4: Run all tests**

Run: `./vendor/bin/sail artisan test`
Expected: All pass.

- [ ] **Step 5: Commit**

```bash
git add resources/views/auth/partials/google-button.blade.php resources/views/auth/register.blade.php resources/views/auth/login.blade.php
git commit -m "feat(auth): add Google OAuth button to register and login pages"
```

---

### Task 5: Onboarding checklist query

**Files:**
- Create: `app/Application/Query/GetOnboardingStatus.php`
- Create: `tests/Feature/Onboarding/GetOnboardingStatusTest.php`

- [ ] **Step 1: Write the test**

```php
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
            'customer_email' => 'customer@example.com',
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
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/sail artisan test --filter=GetOnboardingStatusTest`
Expected: FAIL -- class `GetOnboardingStatus` not found.

- [ ] **Step 3: Implement the query**

Create `app/Application/Query/GetOnboardingStatus.php`:

```php
<?php

namespace App\Application\Query;

use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use Domain\Shared\ValueObject\TenantId;

class GetOnboardingStatus
{
    public function execute(TenantId $tenantId): array
    {
        $profiles = BusinessProfileModel::where('tenant_id', $tenantId->value)->get();

        $hasProfile = $profiles->isNotEmpty();
        $hasGoogleLink = $profiles->contains(fn ($p) => $p->google_review_link !== null);
        $hasSentRequest = $hasProfile && ReviewRequestModel::whereIn(
            'business_profile_id',
            $profiles->pluck('id')
        )->exists();

        $firstProfileId = $profiles->first()?->id;

        $steps = [
            [
                'label' => 'Create your first business profile',
                'done' => $hasProfile,
                'url' => route('business-profiles.create'),
            ],
            [
                'label' => 'Add your Google review link',
                'done' => $hasGoogleLink,
                'url' => $firstProfileId
                    ? route('business-profiles.edit', $firstProfileId)
                    : route('business-profiles.create'),
            ],
            [
                'label' => 'Send your first review request',
                'done' => $hasSentRequest,
                'url' => $firstProfileId
                    ? route('business-profiles.show', $firstProfileId)
                    : route('business-profiles.create'),
            ],
        ];

        return [
            'completed' => $hasProfile && $hasGoogleLink && $hasSentRequest,
            'steps' => $steps,
        ];
    }
}
```

- [ ] **Step 4: Run tests**

Run: `./vendor/bin/sail artisan test --filter=GetOnboardingStatusTest`
Expected: All 4 tests pass.

- [ ] **Step 5: Commit**

```bash
git add app/Application/Query/GetOnboardingStatus.php tests/Feature/Onboarding/GetOnboardingStatusTest.php
git commit -m "feat(onboarding): add GetOnboardingStatus query"
```

---

### Task 6: Wire onboarding into dashboard

**Files:**
- Modify: `app/Http/Controllers/Dashboard/DashboardController.php`
- Modify: `resources/views/dashboard/index.blade.php`

- [ ] **Step 1: Update the dashboard controller**

Replace `app/Http/Controllers/Dashboard/DashboardController.php` with:

```php
<?php

namespace App\Http\Controllers\Dashboard;

use App\Application\Query\GetDashboardStats;
use App\Application\Query\GetBusinessProfiles;
use App\Application\Query\GetOnboardingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private GetDashboardStats $getDashboardStats,
        private GetBusinessProfiles $getBusinessProfiles,
        private GetOnboardingStatus $getOnboardingStatus,
    ) {}

    public function index(Request $request)
    {
        $stats = $this->getDashboardStats->execute($request->get('tenant_id'));
        $profiles = $this->getBusinessProfiles->execute($request->get('tenant_id'));
        $onboarding = $this->getOnboardingStatus->execute($request->get('tenant_id'));

        return view('dashboard.index', compact('stats', 'profiles', 'onboarding'));
    }
}
```

- [ ] **Step 2: Add onboarding card to dashboard view**

In `resources/views/dashboard/index.blade.php`, add the onboarding card right after the opening `<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">` and before the stats grid:

```blade
@unless($onboarding['completed'])
    <div x-data="{ dismissed: false }" x-show="!dismissed" x-transition class="mb-8 bg-indigo-50 border border-indigo-100 rounded-lg p-6">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-lg font-semibold text-indigo-900">Get started with QuickFeedback</h3>
                <p class="mt-1 text-sm text-indigo-700">Complete these steps to start collecting reviews.</p>
            </div>
            <button @click="dismissed = true" class="text-indigo-400 hover:text-indigo-600" aria-label="Dismiss">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <ul class="mt-4 space-y-3">
            @foreach($onboarding['steps'] as $step)
                <li class="flex items-center gap-3">
                    @if($step['done'])
                        <svg class="w-6 h-6 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span class="text-sm text-indigo-400 line-through">{{ $step['label'] }}</span>
                    @else
                        <svg class="w-6 h-6 text-indigo-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                        <a href="{{ $step['url'] }}" class="text-sm font-medium text-indigo-700 hover:text-indigo-900 hover:underline">{{ $step['label'] }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endunless
```

- [ ] **Step 3: Run all tests**

Run: `./vendor/bin/sail artisan test`
Expected: All pass.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Dashboard/DashboardController.php resources/views/dashboard/index.blade.php
git commit -m "feat(onboarding): add getting-started checklist to dashboard"
```

---

### Task 7: Run full test suite and format

- [ ] **Step 1: Run Pint**

Run: `./vendor/bin/sail exec laravel.test ./vendor/bin/pint`

- [ ] **Step 2: Run full test suite**

Run: `./vendor/bin/sail composer test`
Expected: All tests pass.

- [ ] **Step 3: Commit formatting if needed**

```bash
git add -A
git commit -m "chore: apply Pint formatting"
```
