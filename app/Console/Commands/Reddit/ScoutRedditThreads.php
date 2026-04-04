<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\ScoutThreads;
use Illuminate\Console\Command;

class ScoutRedditThreads extends Command
{
    protected $signature = 'reddit:scout';

    protected $description = 'Scout Reddit for relevant threads across target subreddits';

    public function handle(ScoutThreads $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $mode = $command->hasApiCredentials()
            ? 'API'
            : 'public scraper (no API credentials)';

        $this->info("Scouting Reddit for new threads via {$mode}...");

        $count = $command->execute();

        $this->info("Found {$count} new threads.");

        return self::SUCCESS;
    }
}
