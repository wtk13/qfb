<?php

namespace Tests\Feature;

use App\Infrastructure\Mail\ReviewRequestMail;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReviewRequestWebhookTest extends TestCase
{
    use RefreshDatabase;

    private TenantModel $tenant;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantModel::create(['name' => 'T', 'slug' => 't', 'trial_ends_at' => now()->addDays(10)]);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Acme Plumbing',
            'slug' => 'acme-plumbing',
        ]);
    }

    private function hook(array $payload)
    {
        return $this->postJson("/webhooks/review-requests/{$this->profile->webhook_token}", $payload);
    }

    public function test_new_profiles_get_a_secret_webhook_token(): void
    {
        $this->assertSame(40, strlen($this->profile->webhook_token));
    }

    public function test_sends_a_review_request_for_a_valid_token(): void
    {
        Mail::fake();

        $this->hook(['email' => 'customer@example.com', 'name' => 'Pat'])->assertStatus(202);

        $this->assertDatabaseHas('review_requests', [
            'business_profile_id' => $this->profile->id,
            'recipient_email' => 'customer@example.com',
        ]);
        Mail::assertSent(ReviewRequestMail::class, fn (ReviewRequestMail $mail) => $mail->hasTo('customer@example.com'));
    }

    public function test_accepts_form_encoded_payloads(): void
    {
        Mail::fake();

        $this->post("/webhooks/review-requests/{$this->profile->webhook_token}", ['email' => 'form@example.com'])
            ->assertStatus(202);

        $this->assertDatabaseHas('review_requests', ['recipient_email' => 'form@example.com']);
    }

    public function test_unknown_token_is_rejected(): void
    {
        $this->postJson('/webhooks/review-requests/'.str_repeat('x', 40), ['email' => 'a@example.com'])
            ->assertNotFound();
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->hook(['email' => 'not-an-email'])->assertUnprocessable();

        $this->assertSame(0, ReviewRequestModel::count());
    }

    public function test_inactive_subscription_is_rejected(): void
    {
        $this->tenant->update(['trial_ends_at' => now()->subDay()]);

        $this->hook(['email' => 'customer@example.com'])->assertStatus(402);

        $this->assertSame(0, ReviewRequestModel::count());
    }

    public function test_same_customer_is_not_asked_twice_within_30_days(): void
    {
        Mail::fake();

        $this->hook(['email' => 'repeat@example.com'])->assertStatus(202);
        $this->hook(['email' => 'Repeat@Example.com'])->assertOk()->assertJson(['status' => 'skipped']);

        $this->assertSame(1, ReviewRequestModel::count());
    }

    public function test_profile_page_shows_the_webhook_url(): void
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($user)
            ->get("/business-profiles/{$this->profile->id}")
            ->assertOk()
            ->assertSee(route('webhooks.review-requests', $this->profile->webhook_token));
    }
}
