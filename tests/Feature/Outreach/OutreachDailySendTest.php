<?php

namespace Tests\Feature\Outreach;

use App\Infrastructure\Mail\OutreachMail;
use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OutreachDailySendTest extends TestCase
{
    use RefreshDatabase;

    private function lead(string $placeId = 'lead-place'): OutreachLeadModel
    {
        return OutreachLeadModel::create([
            'business_name' => 'Joe Plumbing',
            'email' => 'joe@joeplumbing.example',
            'place_id' => $placeId,
            'reviews' => 37,
            'rating' => 4.6,
            'category' => 'plumber',
            'city' => 'Tampa',
            'email_status' => 'verified',
            'outreach_status' => 'new',
        ]);
    }

    public function test_email_quotes_the_real_top_three_review_counts(): void
    {
        Mail::fake();
        OutreachCampaignModel::create([
            'category' => 'plumber',
            'city' => 'Tampa',
            'top_competitors' => [
                ['place_id' => 'p1', 'name' => 'Best Plumbing', 'reviews' => 1203],
                ['place_id' => 'p2', 'name' => 'Rapid Rooter', 'reviews' => 840],
                ['place_id' => 'p3', 'name' => 'Pipe Pros', 'reviews' => 612],
            ],
        ]);
        $this->lead();

        $this->artisan('outreach:daily-send', ['--delay' => 0])->assertSuccessful();

        Mail::assertSent(OutreachMail::class, function (OutreachMail $mail) {
            return str_contains($mail->emailBody, '"plumber in Tampa"')
                && str_contains($mail->emailBody, '1,203, 840 and 612 reviews')
                && str_contains($mail->emailBody, 'Joe Plumbing has 37')
                && ! str_contains($mail->emailBody, '30-50')
                && ! str_contains($mail->emailBody.$mail->emailSubject, '—');
        });
    }

    public function test_email_without_benchmark_makes_no_comparison_claim(): void
    {
        Mail::fake();
        $this->lead();

        $this->artisan('outreach:daily-send', ['--delay' => 0])->assertSuccessful();

        Mail::assertSent(OutreachMail::class, fn (OutreachMail $mail) => str_contains($mail->emailBody, '37 reviews at 4.6 stars')
            && ! str_contains($mail->emailBody, 'most have')
            && ! str_contains($mail->emailBody, 'That gap'));
    }

    public function test_lead_already_in_the_top_three_gets_no_comparison(): void
    {
        Mail::fake();
        OutreachCampaignModel::create([
            'category' => 'plumber',
            'city' => 'Tampa',
            'top_competitors' => [
                ['place_id' => 'lead-place', 'name' => 'Joe Plumbing', 'reviews' => 37],
                ['place_id' => 'p2', 'name' => 'Rapid Rooter', 'reviews' => 840],
                ['place_id' => 'p3', 'name' => 'Pipe Pros', 'reviews' => 612],
            ],
        ]);
        $this->lead();

        $this->artisan('outreach:daily-send', ['--delay' => 0])->assertSuccessful();

        Mail::assertSent(OutreachMail::class, fn (OutreachMail $mail) => str_contains($mail->emailBody, '37 reviews at 4.6 stars'));
    }
}
