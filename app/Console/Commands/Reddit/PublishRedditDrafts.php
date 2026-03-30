<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\PublishApprovedDrafts;
use Illuminate\Console\Command;

class PublishRedditDrafts extends Command
{
    protected $signature = 'reddit:publish';

    protected $description = 'Publish approved Reddit drafts';

    public function handle(PublishApprovedDrafts $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Publishing approved drafts...');

        $count = $command->execute();

        $this->info("Published {$count} drafts.");

        return self::SUCCESS;
    }
}
