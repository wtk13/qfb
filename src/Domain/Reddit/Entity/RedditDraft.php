<?php

declare(strict_types=1);

namespace Domain\Reddit\Entity;

use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftStatus;
use Domain\Reddit\ValueObject\DraftType;

final class RedditDraft
{
    public function __construct(
        public readonly int $id,
        public readonly ?int $threadId,
        public readonly int $subredditId,
        public readonly DraftType $type,
        public readonly ContentCategory $contentCategory,
        public readonly ?string $title,
        public string $body,
        public DraftStatus $status,
        public ?string $redditThingId,
        public ?\DateTimeImmutable $publishedAt,
        public ?\DateTimeImmutable $approvedAt,
        public ?\DateTimeImmutable $rejectedAt,
        public ?string $rejectionReason,
        public ?int $redditScore,
        public ?int $redditNumComments,
        public readonly \DateTimeImmutable $createdAt,
    ) {}

    public function approve(): void
    {
        $this->status = DraftStatus::Approved;
        $this->approvedAt = new \DateTimeImmutable();
    }

    public function reject(string $reason): void
    {
        $this->status = DraftStatus::Rejected;
        $this->rejectedAt = new \DateTimeImmutable();
        $this->rejectionReason = $reason;
    }

    public function markPublished(string $redditThingId): void
    {
        $this->status = DraftStatus::Published;
        $this->redditThingId = $redditThingId;
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function markFailed(): void
    {
        $this->status = DraftStatus::Failed;
    }

    public function retry(): void
    {
        $this->status = DraftStatus::Approved;
    }
}
