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
                    'user_ratings_total' => 25,
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

    public function test_stores_the_top_three_search_results_as_the_niche_benchmark(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/place/textsearch/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    ['place_id' => 'p1', 'name' => 'Best Plumbing', 'user_ratings_total' => 1203, 'rating' => 4.9],
                    ['place_id' => 'p2', 'name' => 'Rapid Rooter', 'user_ratings_total' => 840, 'rating' => 4.8],
                    ['place_id' => 'p3', 'name' => 'Pipe Pros', 'user_ratings_total' => 612, 'rating' => 4.7],
                    ['place_id' => 'p4', 'name' => 'Small Guy', 'user_ratings_total' => 40, 'rating' => 4.9],
                ],
            ]),
            'maps.googleapis.com/maps/api/place/details/*' => Http::response(['status' => 'OK', 'result' => []]),
            '*' => Http::response(''),
        ]);

        $this->artisan('outreach:weekly')->assertSuccessful();

        $campaign = \App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel::firstOrFail();
        $this->assertSame(
            [['place_id' => 'p1', 'name' => 'Best Plumbing', 'reviews' => 1203], ['place_id' => 'p2', 'name' => 'Rapid Rooter', 'reviews' => 840], ['place_id' => 'p3', 'name' => 'Pipe Pros', 'reviews' => 612]],
            $campaign->top_competitors,
        );
    }

    public function test_skips_businesses_outside_the_review_range_without_a_details_call(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/place/textsearch/*' => Http::response([
                'status' => 'OK',
                'results' => [
                    ['place_id' => 'tiny', 'name' => 'Tiny', 'user_ratings_total' => 3],
                    ['place_id' => 'huge', 'name' => 'Huge', 'user_ratings_total' => 900],
                ],
            ]),
            '*' => Http::response(''),
        ]);

        $this->artisan('outreach:weekly')->assertSuccessful();

        $this->assertSame(0, OutreachLeadModel::count());
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'place/details'));
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
