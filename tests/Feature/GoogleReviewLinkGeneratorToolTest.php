<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleReviewLinkGeneratorToolTest extends TestCase
{
    public function test_tool_page_loads(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertStatus(200);
        $response->assertSee('Google Review Link Generator');
    }

    public function test_tool_page_has_seo_title(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('<title>Free Google Review Link Generator', false);
    }

    public function test_tool_page_has_meta_description(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('meta name="description" content="Generate your direct Google review link instantly. Free tool', false);
    }

    public function test_tool_page_has_canonical_url(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('rel="canonical" href="', false);
        $response->assertSee('/tools/google-review-link-generator', false);
    }

    public function test_tool_page_has_open_graph_tags(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('og:title', false);
        $response->assertSee('og:description', false);
        $response->assertSee('og:type" content="website"', false);
        $response->assertSee('og:image', false);
        $response->assertSee('og:url', false);
    }

    public function test_tool_page_has_twitter_card_tags(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('twitter:card" content="summary_large_image"', false);
        $response->assertSee('twitter:title', false);
        $response->assertSee('twitter:description', false);
        $response->assertSee('twitter:image', false);
    }

    public function test_tool_page_has_web_application_schema(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('application/ld+json', false);
        $response->assertSee('WebApplication', false);
        $response->assertSee('"price": "0"', false);
    }

    public function test_tool_page_has_faq_schema(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('FAQPage', false);
        $response->assertSee('What is a Google Place ID?', false);
    }

    public function test_tool_page_has_place_id_input(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('id="place-id"', false);
        $response->assertSee('x-model="placeId"', false);
    }

    public function test_tool_page_has_generate_button(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('Generate');
        $response->assertSee('@click="generate()"', false);
    }

    public function test_tool_page_has_place_id_finder_link(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('Google Maps');
        $response->assertSee('Place ID Finder');
        $response->assertSee('developers.google.com/maps/documentation/places/web-service/place-id', false);
    }

    public function test_tool_page_has_copy_button_with_aria_label(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('aria-label="Copy review link to clipboard"', false);
    }

    public function test_tool_page_has_cta_section(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('You have the link. Now automate sending it.');
        $response->assertSee('Start Your Free 14-Day Trial');
    }

    public function test_tool_page_cta_links_to_register(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee(route('register'), false);
    }

    public function test_tool_page_has_faq_section(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('Frequently asked questions');
        $response->assertSee('What is a Google Place ID?');
        $response->assertSee('Is this tool really free?');
        $response->assertSee('How do I use my Google review link?');
        $response->assertSee('Can I automate sending this link to customers?');
    }

    public function test_tool_page_faq_has_aria_attributes(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('aria-controls="faq-1"', false);
        $response->assertSee('aria-controls="faq-2"', false);
        $response->assertSee('aria-controls="faq-3"', false);
        $response->assertSee('aria-controls="faq-4"', false);
        $response->assertSee('id="faq-1" role="region"', false);
    }

    public function test_tool_page_has_internal_link_to_blog_post(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee(route('blog.show', 'google-review-link'), false);
    }

    public function test_tool_page_has_navbar_with_navigation(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('aria-label="Main navigation"', false);
        $response->assertSee(route('blog.index'), false);
        $response->assertSee(route('login'), false);
    }

    public function test_tool_page_has_footer(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('All rights reserved.');
        $response->assertSee(route('privacy'), false);
        $response->assertSee(route('terms'), false);
    }

    public function test_tool_page_has_main_landmark(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('<main>', false);
        $response->assertSee('</main>', false);
    }

    public function test_tool_page_has_x_cloak_css(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('[x-cloak] { display: none !important; }', false);
    }

    public function test_tool_page_has_review_link_generator_script(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('function reviewLinkGenerator()', false);
    }

    public function test_tool_page_has_clipboard_fallback(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('fallbackCopy(', false);
        $response->assertSee('document.execCommand', false);
    }

    public function test_tool_page_has_place_id_validation(): void
    {
        $response = $this->get('/tools/google-review-link-generator');

        $response->assertSee('extractPlaceId(', false);
    }

    public function test_tool_page_is_in_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertSee('/tools/google-review-link-generator', false);
    }

    public function test_tool_route_is_named(): void
    {
        $this->assertEquals(
            url('/tools/google-review-link-generator'),
            route('tools.google-review-link-generator')
        );
    }

    public function test_blog_post_links_to_tool(): void
    {
        $response = $this->get('/blog/google-review-link');

        $response->assertSee(route('tools.google-review-link-generator'), false);
        $response->assertSee('free Google Review Link Generator', false);
    }
}
