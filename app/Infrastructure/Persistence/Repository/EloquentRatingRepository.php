<?php

namespace App\Infrastructure\Persistence\Repository;

use App\Infrastructure\Persistence\Eloquent\RatingModel;
use Domain\Feedback\Entity\Rating;
use Domain\Feedback\Port\RatingRepositoryInterface;
use Domain\Feedback\ValueObject\Score;
use Domain\Feedback\ValueObject\Source;

class EloquentRatingRepository implements RatingRepositoryInterface
{
    public function findById(string $id): ?Rating
    {
        $model = RatingModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    public function findByBusinessProfileId(string $businessProfileId): array
    {
        return RatingModel::where('business_profile_id', $businessProfileId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (RatingModel $m) => $this->toDomain($m))
            ->all();
    }

    public function save(Rating $rating): void
    {
        RatingModel::updateOrCreate(
            ['id' => $rating->id],
            [
                'business_profile_id' => $rating->businessProfileId,
                'review_request_id' => $rating->reviewRequestId,
                'score' => $rating->score->value,
                'source' => $rating->source->value,
            ]
        );
    }

    public function countByBusinessProfileId(string $businessProfileId): int
    {
        return RatingModel::where('business_profile_id', $businessProfileId)->count();
    }

    public function averageScoreByBusinessProfileId(string $businessProfileId): ?float
    {
        $avg = RatingModel::where('business_profile_id', $businessProfileId)->avg('score');

        return $avg !== null ? round((float) $avg, 2) : null;
    }

    private function toDomain(RatingModel $model): Rating
    {
        return new Rating(
            id: $model->id,
            businessProfileId: $model->business_profile_id,
            reviewRequestId: $model->review_request_id,
            score: new Score((int) $model->score),
            source: Source::from($model->source),
            createdAt: \DateTimeImmutable::createFromMutable($model->created_at),
        );
    }
}
