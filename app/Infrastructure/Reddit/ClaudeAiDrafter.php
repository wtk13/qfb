<?php

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Entity\RedditThread;
use Domain\Reddit\Port\AiDrafterInterface;
use Domain\Reddit\ValueObject\ContentCategory;
use Domain\Reddit\ValueObject\DraftType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiDrafter implements AiDrafterInterface
{
    public function generateDraft(
        RedditThread $thread,
        string $subredditName,
        ?array $subredditRules,
        ContentCategory $contentCategory,
        DraftType $draftType,
    ): string {
        $maxWords = $draftType === DraftType::Comment ? 300 : 500;
        $rulesText = $subredditRules ? 'Subreddit rules: ' . json_encode($subredditRules) : 'No special subreddit rules.';

        $brandInstruction = $contentCategory === ContentCategory::Brand
            ? 'You may mention QuickFeedback naturally. Always disclose you built it. Always mention competitors fairly. Always provide a free/manual alternative.'
            : 'Do NOT mention QuickFeedback or any specific product.';

        $prompt = <<<PROMPT
You are writing a Reddit {$draftType->value} as a knowledgeable person who helps local businesses get more Google reviews. You are NOT a bot. You are a real person who happens to have expertise in this area.

Rules:
- Write conversationally, like a real Reddit user
- Be specific, use numbers and examples
- Reference the thread's specific situation
- Match the subreddit's culture and tone
- Keep it under {$maxWords} words
- End comments with an offer to elaborate, not a CTA
- {$rulesText}
- {$brandInstruction}

Content category: {$contentCategory->value}
Thread type: {$thread->threadType->value}
Subreddit: r/{$subredditName}

Thread:
Title: {$thread->title}
Body: {$thread->body}

Write the response:
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('reddit.anthropic_api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('reddit.drafter_model'),
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API draft failed', ['status' => $response->status()]);
            throw new \RuntimeException('Failed to generate draft: ' . $response->status());
        }

        return $response->json('content.0.text', '');
    }
}
