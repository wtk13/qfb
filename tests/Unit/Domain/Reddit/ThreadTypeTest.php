<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\ThreadType;
use PHPUnit\Framework\TestCase;

class ThreadTypeTest extends TestCase
{
    public function test_classifies_how_to_get_reviews(): void
    {
        $this->assertSame(ThreadType::HowToGetReviews, ThreadType::classify('How to get more Google reviews?'));
        $this->assertSame(ThreadType::HowToGetReviews, ThreadType::classify('Tips to increase review count'));
    }

    public function test_classifies_negative_review_help(): void
    {
        $this->assertSame(ThreadType::NegativeReviewHelp, ThreadType::classify('How do I handle a bad review?'));
        $this->assertSame(ThreadType::NegativeReviewHelp, ThreadType::classify('Got a 1-star review, help'));
    }

    public function test_classifies_starting_business(): void
    {
        $this->assertSame(ThreadType::StartingBusiness, ThreadType::classify('Starting a new business, any advice?'));
    }

    public function test_classifies_tool_recommendation(): void
    {
        $this->assertSame(ThreadType::ToolRecommendation, ThreadType::classify('Best tool for review management?'));
        $this->assertSame(ThreadType::ToolRecommendation, ThreadType::classify('Software to manage customer reviews'));
    }

    public function test_classifies_local_seo(): void
    {
        $this->assertSame(ThreadType::LocalSeo, ThreadType::classify('How to improve local SEO'));
        $this->assertSame(ThreadType::LocalSeo, ThreadType::classify('Struggling with Google ranking'));
    }

    public function test_defaults_to_general(): void
    {
        $this->assertSame(ThreadType::General, ThreadType::classify('Random discussion topic'));
    }

    public function test_uses_body_for_classification(): void
    {
        $this->assertSame(
            ThreadType::HowToGetReviews,
            ThreadType::classify('Help needed', 'I want to get more reviews for my business')
        );
    }
}
