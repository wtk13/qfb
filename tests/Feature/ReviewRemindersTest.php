<?php

namespace Tests\Feature;

use App\Infrastructure\Mail\ReviewRequestMail;
use App\Infrastructure\Persistence\Eloquent\BusinessProfileModel;
use App\Infrastructure\Persistence\Eloquent\ReviewRequestModel;
use App\Infrastructure\Persistence\Eloquent\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReviewRemindersTest extends TestCase
{
    use RefreshDatabase;

    private BusinessProfileModel $profile;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = TenantModel::create(['name' => 'T', 'slug' => 't']);
        $this->profile = BusinessProfileModel::create([
            'tenant_id' => $tenant->id,
            'name' => 'Acme Plumbing',
            'slug' => 'acme-plumbing',
        ]);
    }

    private function request(string $email, string $status, int $sentDaysAgo): ReviewRequestModel
    {
        return ReviewRequestModel::create([
            'id' => (string) Str::uuid(),
            'business_profile_id' => $this->profile->id,
            'recipient_email' => $email,
            'status' => $status,
            'token' => Str::random(32),
            'sent_at' => now()->subDays($sentDaysAgo),
        ]);
    }

    public function test_reminds_customers_who_did_not_open_the_request_once(): void
    {
        Mail::fake();
        $due = $this->request('waiting@example.com', 'sent', 4);
        $sentAt = $due->sent_at;

        $this->artisan('reviews:send-reminders')->assertSuccessful();
        $this->artisan('reviews:send-reminders')->assertSuccessful();

        Mail::assertSent(ReviewRequestMail::class, 1);
        Mail::assertSent(ReviewRequestMail::class, fn (ReviewRequestMail $mail) => $mail->hasTo('waiting@example.com')
            && $mail->isReminder
            && str_contains($mail->render(), "/rate/acme-plumbing/{$due->token}"));
        $this->assertNotNull($due->fresh()->reminder_sent_at);
        $this->assertSame('sent', $due->fresh()->status);
        $this->assertEquals($sentAt, $due->fresh()->sent_at);
    }

    public function test_leaves_recent_answered_and_old_requests_alone(): void
    {
        Mail::fake();
        $this->request('recent@example.com', 'sent', 1);
        $this->request('clicked@example.com', 'clicked', 4);
        $this->request('old@example.com', 'sent', 20);

        $this->artisan('reviews:send-reminders')->assertSuccessful();

        Mail::assertNotSent(ReviewRequestMail::class);
    }
}
