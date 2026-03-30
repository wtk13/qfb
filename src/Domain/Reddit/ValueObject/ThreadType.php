<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

enum ThreadType: string
{
    case HowToGetReviews = 'how_to_get_reviews';
    case NegativeReviewHelp = 'negative_review_help';
    case StartingBusiness = 'starting_business';
    case ToolRecommendation = 'tool_recommendation';
    case LocalSeo = 'local_seo';
    case General = 'general';

    public static function classify(string $title, ?string $body = null): self
    {
        $text = strtolower($title.' '.($body ?? ''));

        $patterns = [
            'how_to_get_reviews' => '/\b(get|more|increase)\b.*\breview/',
            'negative_review_help' => '/\b(negative|bad|terrible|1.star)\b.*\breview/',
            'starting_business' => '/\b(starting|new|launch)\b.*\bbusiness/',
            'tool_recommendation' => '/\b(tool|software|recommend|app)\b.*\breview/',
            'local_seo' => '/\blocal\s+seo\b|\bgoogle\s+rank/',
        ];

        foreach ($patterns as $value => $pattern) {
            if (preg_match($pattern, $text)) {
                return self::from($value);
            }
        }

        return self::General;
    }
}
