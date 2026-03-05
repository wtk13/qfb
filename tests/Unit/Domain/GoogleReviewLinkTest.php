<?php

namespace Tests\Unit\Domain;

use Domain\Business\ValueObject\GoogleReviewLink;
use PHPUnit\Framework\TestCase;

class GoogleReviewLinkTest extends TestCase
{
    public function test_valid_google_link(): void
    {
        $link = new GoogleReviewLink('https://www.google.com/maps/place/test');
        $this->assertStringContainsString('google.com', $link->value);
    }

    public function test_invalid_url_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GoogleReviewLink('not-a-url');
    }

    public function test_non_google_url_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GoogleReviewLink('https://www.example.com/review');
    }
}
