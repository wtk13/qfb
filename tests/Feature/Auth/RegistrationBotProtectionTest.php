<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationBotProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_ATTEMPTS_PER_HOUR = 10;

    public function test_registration_is_rate_limited_per_client_ip(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7']);

        for ($i = 0; $i < self::REGISTER_ATTEMPTS_PER_HOUR; $i++) {
            $this->post('/register', [])->assertStatus(302);
        }

        $this->post('/register', [])->assertStatus(429);
    }

    public function test_rate_limit_is_keyed_by_real_client_ip_behind_cloudflare(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.68.150.80']);

        for ($i = 0; $i < self::REGISTER_ATTEMPTS_PER_HOUR; $i++) {
            $this->withHeader('X-Forwarded-For', '198.51.100.1')->post('/register', []);
        }

        $this->withHeader('X-Forwarded-For', '198.51.100.1')->post('/register', [])->assertStatus(429);
        $this->withHeader('X-Forwarded-For', '198.51.100.2')->post('/register', [])->assertStatus(302);
    }

    public function test_forwarded_ip_from_untrusted_source_does_not_bypass_rate_limit(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);

        for ($i = 0; $i <= self::REGISTER_ATTEMPTS_PER_HOUR; $i++) {
            $response = $this->withHeader('X-Forwarded-For', "198.51.100.{$i}")->post('/register', []);
        }

        $response->assertStatus(429);
    }

    public function test_register_page_omits_turnstile_widget_when_not_configured(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertDontSee('challenges.cloudflare.com');
    }

    public function test_register_page_renders_turnstile_widget_when_configured(): void
    {
        $this->enableTurnstile();

        $this->get('/register')
            ->assertOk()
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false)
            ->assertSee('data-sitekey="test-site-key"', false);
    }

    public function test_registration_requires_turnstile_token_when_configured(): void
    {
        $this->enableTurnstile();
        Http::fake();

        $this->post('/register', $this->validPayload())
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
        Http::assertNothingSent();
    }

    public function test_registration_is_rejected_when_turnstile_verification_fails(): void
    {
        $this->enableTurnstile();
        Notification::fake();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $this->post('/register', $this->validPayload() + ['cf-turnstile-response' => 'bot-token'])
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
        Notification::assertNothingSent();
    }

    public function test_registration_is_rejected_when_turnstile_is_unreachable(): void
    {
        $this->enableTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::failedConnection(),
        ]);

        $this->post('/register', $this->validPayload() + ['cf-turnstile-response' => 'some-token'])
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
    }

    public function test_registration_succeeds_when_turnstile_verification_passes(): void
    {
        $this->enableTurnstile();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->post('/register', $this->validPayload() + ['cf-turnstile-response' => 'human-token'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'human-token'
            && $request['remoteip'] === '198.51.100.7');
    }

    private function enableTurnstile(): void
    {
        config([
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret_key' => 'test-secret-key',
        ]);
    }

    private function validPayload(): array
    {
        return [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }
}
