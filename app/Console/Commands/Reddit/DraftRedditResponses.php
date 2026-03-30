<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\DraftResponses;
use Illuminate\Console\Command;

class DraftRedditResponses extends Command
{
    protected $signature = 'reddit:draft';

    protected $description = 'Generate AI drafts for new Reddit threads';

    public function handle(DraftResponses $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Generating drafts...');

        $count = $command->execute();

        $this->info("Generated {$count} drafts.");

        return self::SUCCESS;
    }
}
