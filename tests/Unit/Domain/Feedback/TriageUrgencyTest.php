<?php

namespace Tests\Unit\Domain\Feedback;

use Domain\Feedback\ValueObject\TriageUrgency;
use PHPUnit\Framework\TestCase;

class TriageUrgencyTest extends TestCase
{
    public function test_has_all_expected_cases(): void
    {
        $expected = ['low', 'medium', 'high'];

        $actual = array_map(fn (TriageUrgency $u) => $u->value, TriageUrgency::cases());

        $this->assertSame($expected, $actual);
    }

    public function test_can_be_created_from_string(): void
    {
        $this->assertSame(TriageUrgency::High, TriageUrgency::from('high'));
    }
}
