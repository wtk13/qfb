<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

interface RedditApiInterface
{
    /**
     * @return array<array{id: string, title: string, selftext: string|null, author: string, url: string, score: int, num_comments: int, created_utc: int}>
     */
    public function searchSubreddit(string $subreddit, string $query, int $limit = 25): array;

    public function submitComment(string $parentThingId, string $body): string;

    public function submitPost(string $subreddit, string $title, string $body): string;
}
