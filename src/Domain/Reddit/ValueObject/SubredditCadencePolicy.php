<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class SubredditCadencePolicy
{
    public function __construct(
        private int $maxPostsPerWeek,
        private int $maxCommentsPerWeek,
        private int $postsThisWeek,
        private int $commentsThisWeek,
    ) {}

    public function canPost(): bool
    {
        return $this->postsThisWeek < $this->maxPostsPerWeek;
    }

    public function canComment(): bool
    {
        return $this->commentsThisWeek < $this->maxCommentsPerWeek;
    }

    public function canPublish(DraftType $type): bool
    {
        return match ($type) {
            DraftType::Post => $this->canPost(),
            DraftType::Comment => $this->canComment(),
        };
    }
}
