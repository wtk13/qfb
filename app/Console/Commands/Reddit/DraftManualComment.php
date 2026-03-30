<?php

declare(strict_types=1);

namespace App\Console\Commands\Reddit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DraftManualComment extends Command
{
    protected $signature = 'reddit:comment
        {url : Reddit thread URL}
        {--brand : Allow mentioning QuickFeedback}';

    protected $description = 'Draft a comment for a Reddit thread you found manually (no API access needed)';

    public function handle(): int
    {
        $apiKey = config('reddit.anthropic_api_key');
        if (! $apiKey) {
            $this->error('Set ANTHROPIC_API_KEY in .env first.');

            return self::FAILURE;
        }

        $url = $this->argument('url');

        $this->info('Fetching thread...');
        $thread = $this->fetchThread($url);
        if (! $thread) {
            $this->error('Could not fetch thread. Check the URL.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->line("<fg=cyan>r/{$thread['subreddit']}</> - <fg=white>{$thread['title']}</>");
        $this->line("<fg=gray>by u/{$thread['author']} | {$thread['score']} points | {$thread['num_comments']} comments</>");
        if ($thread['body']) {
            $this->newLine();
            $this->line(mb_substr($thread['body'], 0, 500).(mb_strlen($thread['body']) > 500 ? '...' : ''));
        }
        $this->newLine();

        $brandMode = $this->option('brand');
        $brandInstruction = $brandMode
            ? 'You may mention QuickFeedback naturally. Always disclose you built it. Always mention competitors fairly. Always provide a free/manual alternative.'
            : 'Do NOT mention QuickFeedback or any specific product. This is a pure value comment to build credibility.';

        $prompt = <<<PROMPT
You are writing a Reddit comment as a knowledgeable person who helps local businesses get more Google reviews. You are a real person building expertise in this area, participating genuinely in the community.

Rules:
- Write conversationally, like a real Reddit user
- Be specific, use numbers and examples where possible
- Reference the thread's specific situation
- Keep it under 200 words (you're a new account, don't write essays yet)
- Sound natural - no corporate speak, no bullet-point lists unless the thread calls for it
- End with something conversational, not a CTA
- {$brandInstruction}

Subreddit: r/{$thread['subreddit']}

Thread:
Title: {$thread['title']}
Body: {$thread['body']}

Write the comment:
PROMPT;

        $this->info('Generating draft...');

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
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
            $this->error('Claude API failed: '.$response->status());

            return self::FAILURE;
        }

        $draft = $response->json('content.0.text', '');

        $this->newLine();
        $this->line('<fg=green>--- Draft Comment ---</>');
        $this->newLine();
        $this->line($draft);
        $this->newLine();
        $this->line('<fg=green>--- End ---</>');
        $this->newLine();
        $this->info('Copy, edit to add your personal voice, then post manually on Reddit.');

        return self::SUCCESS;
    }

    private function fetchThread(string $url): ?array
    {
        // Normalize URL to .json endpoint
        $url = rtrim($url, '/');
        $url = preg_replace('/\?.*$/', '', $url);
        $jsonUrl = $url.'.json';

        $response = Http::withHeaders([
            'User-Agent' => 'QuickFeedback:lurk-helper:v1.0',
        ])->get($jsonUrl);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();
        if (! isset($data[0]['data']['children'][0]['data'])) {
            return null;
        }

        $post = $data[0]['data']['children'][0]['data'];

        return [
            'title' => $post['title'] ?? '',
            'body' => $post['selftext'] ?? '',
            'author' => $post['author'] ?? '',
            'subreddit' => $post['subreddit'] ?? '',
            'score' => $post['score'] ?? 0,
            'num_comments' => $post['num_comments'] ?? 0,
        ];
    }
}
