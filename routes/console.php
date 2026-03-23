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
