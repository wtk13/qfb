<?php

declare(strict_types=1);

namespace App\Infrastructure\Ai;

use Domain\Feedback\Entity\FeedbackTriage;
use Domain\Feedback\Port\FeedbackTriageServiceInterface;
use Domain\Feedback\ValueObject\TriageCategory;
use Domain\Feedback\ValueObject\TriageUrgency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ClaudeFeedbackTriageService implements FeedbackTriageServiceInterface
{
    public function triage(string $comment, int $score): FeedbackTriage
    {
        $model = config('services.anthropic.triage_model');

        $prompt = <<<PROMPT
You are a customer feedback analyst for a local business. Analyze this negative customer feedback and provide a structured triage.

Customer rating: {$score}/5
Customer comment: {$comment}

Respond with ONLY a JSON object (no markdown, no explanation) with these exact keys:
- "category": one of "staff", "wait_time", "product_quality", "pricing", "environment", "communication", "other"
- "urgency": one of "low", "medium", "high"
  - low: general dissatisfaction, minor complaint
  - medium: specific issue, disappointed but not escalating
  - high: emotional intensity, mentions of legal action, social media threats, health/safety concerns
- "suggested_response": a 2-3 sentence professional response the business owner can send to the customer. Be empathetic and offer to make it right.
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => 512,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->failed()) {
            Log::error('Claude API triage failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Failed to triage feedback: '.$response->status());
        }

        $text = $response->json('content.0.text', '{}');

        $text = preg_replace('/^```(?:json)?\s*\n?/i', '', $text);
        $text = preg_replace('/\n?```\s*$/i', '', $text);

        $data = json_decode(trim($text), true);

        if (! $data || ! isset($data['category'], $data['urgency'], $data['suggested_response'])) {
            Log::error('Claude API triage returned invalid JSON', ['raw' => $text]);
            throw new \RuntimeException('Invalid triage response from LLM');
        }

        $category = TriageCategory::tryFrom($data['category']) ?? TriageCategory::Other;
        $urgency = TriageUrgency::tryFrom($data['urgency']) ?? TriageUrgency::Medium;

        return new FeedbackTriage(
            id: (string) Str::uuid(),
            feedbackId: '',
            category: $category,
            urgency: $urgency,
            suggestedResponse: $data['suggested_response'],
            rawLlmResponse: $text,
            modelUsed: $model,
            triagedAt: new \DateTimeImmutable,
        );
    }
}
