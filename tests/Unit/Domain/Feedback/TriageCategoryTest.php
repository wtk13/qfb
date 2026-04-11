<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\ValueObject\TriageCategory;
use PHPUnit\Framework\TestCase;

class TriageCategoryTest extends TestCase
{
    public function test_has_all_expected_cases(): void
    {
        $expected = ['staff', 'wait_time', 'product_quality', 'pricing', 'environment', 'communication', 'other'];

        $actual = array_map(fn (TriageCategory $c) => $c->value, TriageCategory::cases());

        $this->assertSame($expected, $actual);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertSame(TriageCategory::Staff, TriageCategory::from('staff'));
        $this->assertSame(TriageCategory::Other, TriageCategory::from('other'));
    }

    public function test_try_from_returns_null_for_invalid(): void
    {
        $this->assertNull(TriageCategory::tryFrom('invalid'));
    }
}
