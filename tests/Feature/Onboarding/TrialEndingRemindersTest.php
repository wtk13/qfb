<?php

namespace Tests\Feature\Onboarding;

use App\Infrastructure\Persistence\Eloquent\TenantModel;
use App\Mail\TrialEndingMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrialEndingRemindersTest extends TestCase
{
    use RefreshDatabase;

    private function tenantWithUser(?\DateTimeInterface $trialEndsAt, string $email): TenantModel
    {
        $tenant = TenantModel::create([
            'name' => $email,
            'slug' => md5($email),
            'trial_ends_at' => $trialEndsAt,
        ]);
        User::factory()->create(['tenant_id' => $tenant->id, 'email' => $email]);

        return $tenant;
    }

    public function test_reminds_once_when_trial_ends_within_three_days(): void
    {
        Mail::fake();
        $tenant = $this->tenantWithUser(now()->addDays(2), 'ending@example.com');

        $this->artisan('trial:send-reminders')->assertSuccessful();
        $this->artisan('trial:send-reminders')->assertSuccessful();

        Mail::assertSent(TrialEndingMail::class, 1);
        Mail::assertSent(TrialEndingMail::class, fn (TrialEndingMail $mail) => $mail->hasTo('ending@example.com'));
        $this->assertNotNull($tenant->fresh()->trial_reminder_sent_at);
    }

    public function test_skips_trials_that_end_later_or_already_ended(): void
    {
        Mail::fake();
        $this->tenantWithUser(now()->addDays(10), 'later@example.com');
        $this->tenantWithUser(now()->subDay(), 'ended@example.com');

        $this->artisan('trial:send-reminders')->assertSuccessful();

        Mail::assertNotSent(TrialEndingMail::class);
    }

    public function test_skips_tenants_that_already_subscribed(): void
    {
        Mail::fake();
        $tenant = $this->tenantWithUser(now()->addDays(2), 'paid@example.com');
        $tenant->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test',
            'stripe_status' => 'active',
        ]);

        $this->artisan('trial:send-reminders')->assertSuccessful();

        Mail::assertNotSent(TrialEndingMail::class);
    }

    public function test_skips_users_who_never_verified_their_email(): void
    {
        Mail::fake();
        $tenant = TenantModel::create(['name' => 'x', 'slug' => 'x', 'trial_ends_at' => now()->addDays(2)]);
        User::factory()->unverified()->create(['tenant_id' => $tenant->id]);

        $this->artisan('trial:send-reminders')->assertSuccessful();

        Mail::assertNotSent(TrialEndingMail::class);
    }
}
