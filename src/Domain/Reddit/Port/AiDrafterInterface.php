<?php

declare(strict_types=1);

namespace Domain\Reddit\Port;

use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftType;

interface AiDrafterInterface
{
    public function generateDraft(
        RedditThread $thread,
        string $subredditName,
        ?array $subredditRules,
        ContentCategory $contentCategory,
        DraftType $draftType,
    ): string;
}
