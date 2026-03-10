<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_landing_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Get More 5-Star Google Reviews');
    }

    public function test_privacy_policy_loads(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
    }

    public function test_terms_of_service_loads(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
        $response->assertSee('Terms of Service');
    }

    public function test_landing_page_has_seo_meta_tags(): void
    {
        $response = $this->get('/');

        $response->assertSee('meta name="description"', false);
        $response->assertSee('og:title', false);
        $response->assertSee('application/ld+json', false);
    }

    public function test_invalid_rating_page_returns_404(): void
    {
        $response = $this->get('/rate/nonexistent-slug/fake-token');

        $response->assertStatus(404);
    }
}
