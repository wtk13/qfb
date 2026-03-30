<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditSubreddit;

interface RedditSubredditRepositoryInterface
{
    public function findById(int $id): ?RedditSubreddit;

    /** @return RedditSubreddit[] */
    public function findActive(): array;

    /** @return RedditSubreddit[] */
    public function findAll(): array;

    public function save(RedditSubreddit $subreddit): void;
}
