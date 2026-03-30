<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Outreach pipeline: scrape new niche every Monday, send daily
Schedule::command('outreach:weekly')->weeklyOn(1, '06:00')->withoutOverlapping();
Schedule::command('outreach:daily-send --limit=15 --delay=30')->dailyAt('09:00')->withoutOverlapping();

// Reddit marketing agent
Schedule::command('reddit:scout')->everyTwoHours()->withoutOverlapping();
Schedule::command('reddit:draft')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('reddit:publish')->everyFifteenMinutes()->withoutOverlapping()->runInBackground();
Schedule::command('reddit:strategist')->weeklyOn(0, '10:00')->withoutOverlapping();

// Reddit data retention cleanup (monthly)
Schedule::call(function () {
    app(\Domain\Reddit\Port\RedditThreadRepositoryInterface::class)
        ->purgeOlderThan(new \DateTimeImmutable('-30 days'), [\Domain\Reddit\ValueObject\ThreadStatus::Skipped, \Domain\Reddit\ValueObject\ThreadStatus::Stale]);
    app(\Domain\Reddit\Port\RedditDraftRepositoryInterface::class)
        ->purgeOlderThan(new \DateTimeImmutable('-90 days'), [\Domain\Reddit\ValueObject\DraftStatus::Rejected]);
})->name('reddit:cleanup')->monthly()->withoutOverlapping();
