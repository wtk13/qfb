<?php

namespace App\Infrastructure\Reddit;

use Domain\Reddit\Port\AiStrategistInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeAiStrategist implements AiStrategistInterface
{
    public function analyzeWeeklyMetrics(array $metricsContext): array
    {
        $metricsJson = json_encode($metricsContext);

        $prompt = <<<PROMPT
You are a Reddit marketing strategist for QuickFeedback, a review management SaaS for local businesses.

Analyze these weekly metrics and provide strategic recommendations:

{$metricsJson}

Provide:
1. Overall assessment (2-3 sentences)
2. What's working well
3. What needs improvement
4. Specific recommendations for next week (subreddits to focus on, content types to emphasize)
5. Any phase transition readiness assessment

Format as JSON with keys: summary, working_well, needs_improvement, recommendations, phase_assessment
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('reddit.anthropic_api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('reddit.strategist_model'),
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API strategy report failed', ['status' => $response->status()]);

            return ['error' => 'Failed to generate report: ' . $response->status()];
        }

        return json_decode($response->json('content.0.text', '{}'), true) ?? [];
    }
}
