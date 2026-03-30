<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

final class RedditSubreddit
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $tier,
        public int $maxPostsPerWeek,
        public int $maxCommentsPerWeek,
        public ?array $rulesJson,
        public ?array $keywordsJson,
        public bool $isActive,
    ) {}
}
