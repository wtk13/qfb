<?php

namespace Tests\Feature\Outreach;

use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OutreachWeeklyRotationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google_places.key' => 'test-key']);
    }

    public function test_lead_without_email_on_website_is_not_given_a_guessed_address(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/place/textsearch/*' => Http::response([
                'status' => 'OK',
                'results' => [['place_id' => 'place-no-email']],
            ]),
            'maps.googleapis.com/maps/api/place/details/*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'name' => 'Acme Plumbing',
                    'website' => 'https://acme-plumbing.example/',
                    'user_ratings_total' => 5,
                    'rating' => 4.8,
                ],
            ]),
            '*' => Http::response('<html><body>Call us today!</body></html>'),
        ]);

        $this->artisan('outreach:weekly')->assertSuccessful();

        $lead = OutreachLeadModel::where('place_id', 'place-no-email')->firstOrFail();
        $this->assertNull($lead->email);
        $this->assertSame('invalid', $lead->email_status);
    }

    public function test_pending_leads_from_other_niches_are_verified_too(): void
    {
        // e.g. left pending by outreach:rescrape-emails for a niche that isn't this run's target
        $stranded = OutreachLeadModel::create([
            'business_name' => 'Stranded Lead',
            'email' => 'owner@stranded.invalid',
            'place_id' => 'place-stranded',
            'category' => 'test niche',
            'city' => 'Nowhere',
            'email_status' => 'pending',
            'outreach_status' => 'new',
        ]);

        Http::fake([
            'maps.googleapis.com/*' => Http::response(['status' => 'ZERO_RESULTS', 'results' => []]),
        ]);

        $this->artisan('outreach:weekly')->assertSuccessful();

        $this->assertSame('invalid', $stranded->fresh()->email_status);
    }
}
