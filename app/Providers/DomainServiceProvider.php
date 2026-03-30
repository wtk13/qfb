<?php

namespace App\Providers;

use App\Infrastructure\Billing\CashierSubscriptionService;
use App\Infrastructure\Persistence\Repository\EloquentBusinessProfileRepository;
use App\Infrastructure\Persistence\Repository\EloquentFeedbackRepository;
use App\Infrastructure\Persistence\Repository\EloquentRatingRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditDraftRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditStrategyReportRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditSubredditRepository;
use App\Infrastructure\Persistence\Repository\EloquentRedditThreadRepository;
use App\Infrastructure\Persistence\Repository\EloquentReviewRequestRepository;
use App\Infrastructure\Persistence\Repository\EloquentTenantRepository;
use Domain\Billing\Port\SubscriptionServiceInterface;
use Domain\Business\Port\BusinessProfileRepositoryInterface;
use Domain\Campaign\Port\ReviewRequestRepositoryInterface;
use Domain\Feedback\Port\FeedbackRepositoryInterface;
use Domain\Feedback\Port\RatingRepositoryInterface;
use Domain\Feedback\Service\RatingRoutingService;
use Domain\Identity\Port\TenantRepositoryInterface;
use Domain\Reddit\Port\RedditDraftRepositoryInterface;
use Domain\Reddit\Port\RedditStrategyReportRepositoryInterface;
use Domain\Reddit\Port\RedditSubredditRepositoryInterface;
use Domain\Reddit\Port\RedditThreadRepositoryInterface;
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
        RedditSubredditRepositoryInterface::class => EloquentRedditSubredditRepository::class,
        RedditThreadRepositoryInterface::class => EloquentRedditThreadRepository::class,
        RedditDraftRepositoryInterface::class => EloquentRedditDraftRepository::class,
        RedditStrategyReportRepositoryInterface::class => EloquentRedditStrategyReportRepository::class,
    ];

    public function register(): void
    {
        $this->app->singleton(RatingRoutingService::class);
    }
}
