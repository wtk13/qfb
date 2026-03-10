<?php

namespace App\Providers;

use App\Infrastructure\Billing\CashierSubscriptionService;
use App\Infrastructure\Persistence\Repository\EloquentBusinessProfileRepository;
use App\Infrastructure\Persistence\Repository\EloquentFeedbackRepository;
use App\Infrastructure\Persistence\Repository\EloquentRatingRepository;
use App\Infrastructure\Persistence\Repository\EloquentReviewRequestRepository;
use App\Infrastructure\Persistence\Repository\EloquentTenantRepository;
use Domain\Billing\Port\SubscriptionServiceInterface;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\RatingRepositoryInterface;
use Domain\Feedback\Service\RatingRoutingService;
use Domain\Identity\Port\TenantRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public array $bindings = [
        TenantRepositoryInterface::class => EloquentTenantRepository::class,
        BusinessProfileRepositoryInterface::class => EloquentBusinessProfileRepository::class,
        ReviewRequestRepositoryInterface::class => EloquentReviewRequestRepository::class,
        RatingRepositoryInterface::class => EloquentRatingRepository::class,
        FeedbackRepositoryInterface::class => EloquentFeedbackRepository::class,
        SubscriptionServiceInterface::class => CashierSubscriptionService::class,
    ];

    public function register(): void
    {
        $this->app->singleton(RatingRoutingService::class);
    }
}
