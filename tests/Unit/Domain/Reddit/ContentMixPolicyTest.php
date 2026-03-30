<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\ContentMixPolicy;
use PHPUnit\Framework\TestCase;

class ContentMixPolicyTest extends TestCase
{
    public function test_allows_any_category_when_fewer_than_5_drafts(): void
    {
        $policy = new ContentMixPolicy(['value' => 0, 'discussion' => 0, 'brand' => 2]);

        $this->assertTrue($policy->canGenerate(ContentCategory::Brand));
        $this->assertTrue($policy->canGenerate(ContentCategory::Value));
    }

    public function test_blocks_brand_when_over_10_percent(): void
    {
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 1, 'brand' => 2]);

        $this->assertFalse($policy->canGenerate(ContentCategory::Brand));
    }

    public function test_allows_brand_when_at_or_under_target(): void
    {
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 2, 'brand' => 1]);

        $this->assertTrue($policy->canGenerate(ContentCategory::Brand));
    }

    public function test_suggests_value_when_empty(): void
    {
        $policy = new ContentMixPolicy(['value' => 0, 'discussion' => 0, 'brand' => 0]);

        $this->assertSame(ContentCategory::Value, $policy->suggestCategory());
    }

    public function test_suggests_underrepresented_category(): void
    {
        $policy = new ContentMixPolicy(['value' => 3, 'discussion' => 5, 'brand' => 2]);

        $this->assertSame(ContentCategory::Value, $policy->suggestCategory());
    }

    public function test_ratio_calculation(): void
    {
        $policy = new ContentMixPolicy(['value' => 7, 'discussion' => 2, 'brand' => 1]);

        $this->assertEqualsWithDelta(0.70, $policy->ratio(ContentCategory::Value), 0.01);
        $this->assertEqualsWithDelta(0.20, $policy->ratio(ContentCategory::Discussion), 0.01);
        $this->assertEqualsWithDelta(0.10, $policy->ratio(ContentCategory::Brand), 0.01);
    }
}
