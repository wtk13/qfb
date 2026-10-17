<?php

namespace Tests\Feature\Outreach;

use App\Infrastructure\Persistence\Eloquent\OutreachCampaignModel;
use App\Infrastructure\Persistence\Eloquent\OutreachLeadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutreachLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OutreachLeadModel::create([
            'business_name' => 'Joe Plumbing',
            'place_id' => 'lead-place',
            'reviews' => 37,
            'rating' => 4.6,
            'category' => 'plumber',
            'city' => 'Tampa',
        ]);
    }

    public function test_shows_the_real_top_three_for_the_niche(): void
    {
        OutreachCampaignModel::create([
            'category' => 'plumber',
            'city' => 'Tampa',
            'top_competitors' => [
                ['place_id' => 'p1', 'name' => 'Best Plumbing', 'reviews' => 1203],
                ['place_id' => 'p2', 'name' => 'Rapid Rooter', 'reviews' => 840],
            ],
        ]);

        $this->get('/for/lead-place')
            ->assertOk()
            ->assertSee('Best Plumbing')
            ->assertSee('1,203')
            ->assertDontSee('Top 3 local benchmark (50)');
    }

    public function test_makes_no_benchmark_claim_without_data(): void
    {
        $this->get('/for/lead-place')
            ->assertOk()
            ->assertSee('37 reviews')
            ->assertDontSee('Top 3 local benchmark')
            ->assertDontSee('30&ndash;50+', false);
    }
}
