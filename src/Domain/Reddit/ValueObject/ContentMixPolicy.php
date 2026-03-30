<?php

declare(strict_types=1);

namespace Domain\Reddit\ValueObject;

final readonly class ContentMixPolicy
{
    private const float VALUE_TARGET = 0.70;

    private const float DISCUSSION_TARGET = 0.20;

    private const float BRAND_TARGET = 0.10;

    /**
     * @param  array{value: int, discussion: int, brand: int}  $counts  30-day rolling counts
     */
    public function __construct(
        private array $counts,
    ) {}

    public function total(): int
    {
        return $this->counts['value'] + $this->counts['discussion'] + $this->counts['brand'];
    }

    public function ratio(ContentCategory $category): float
    {
        $total = $this->total();
        if ($total === 0) {
            return 0.0;
        }

        return $this->counts[$category->value] / $total;
    }

    public function canGenerate(ContentCategory $category): bool
    {
        $total = $this->total();

        if ($total < 5) {
            return true;
        }

        $target = match ($category) {
            ContentCategory::Value => self::VALUE_TARGET,
            ContentCategory::Discussion => self::DISCUSSION_TARGET,
            ContentCategory::Brand => self::BRAND_TARGET,
        };

        return $this->ratio($category) <= $target + 0.05;
    }

    public function suggestCategory(): ContentCategory
    {
        $total = $this->total();

        if ($total < 5) {
            return ContentCategory::Value;
        }

        $gaps = [
            ContentCategory::Value->value => self::VALUE_TARGET - $this->ratio(ContentCategory::Value),
            ContentCategory::Discussion->value => self::DISCUSSION_TARGET - $this->ratio(ContentCategory::Discussion),
            ContentCategory::Brand->value => self::BRAND_TARGET - $this->ratio(ContentCategory::Brand),
        ];

        $best = array_keys($gaps, max($gaps))[0];

        return ContentCategory::from($best);
    }
}
