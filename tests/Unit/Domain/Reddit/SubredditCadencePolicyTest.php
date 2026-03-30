<?php

namespace Tests\Unit\Domain\Reddit;

use Domain\Reddit\ValueObject\DraftType;
use Domain\Reddit\ValueObject\SubredditCadencePolicy;
use PHPUnit\Framework\TestCase;

class SubredditCadencePolicyTest extends TestCase
{
    public function test_allows_post_when_under_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 1, commentsThisWeek: 0);

        $this->assertTrue($policy->canPost());
        $this->assertTrue($policy->canPublish(DraftType::Post));
    }

    public function test_blocks_post_when_at_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 2, commentsThisWeek: 0);

        $this->assertFalse($policy->canPost());
        $this->assertFalse($policy->canPublish(DraftType::Post));
    }

    public function test_allows_comment_when_under_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 0, commentsThisWeek: 3);

        $this->assertTrue($policy->canComment());
        $this->assertTrue($policy->canPublish(DraftType::Comment));
    }

    public function test_blocks_comment_when_at_limit(): void
    {
        $policy = new SubredditCadencePolicy(maxPostsPerWeek: 2, maxCommentsPerWeek: 5, postsThisWeek: 0, commentsThisWeek: 5);

        $this->assertFalse($policy->canComment());
        $this->assertFalse($policy->canPublish(DraftType::Comment));
    }
}
