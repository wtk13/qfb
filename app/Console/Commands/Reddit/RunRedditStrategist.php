<?php

namespace App\Console\Commands\Reddit;

use App\Application\Command\Reddit\GenerateStrategyReport;
use Illuminate\Console\Command;

class RunRedditStrategist extends Command
{
    protected $signature = 'reddit:strategist';

    protected $description = 'Generate weekly Reddit strategy report';

    public function handle(GenerateStrategyReport $command): int
    {
        if (! config('reddit.enabled')) {
            $this->info('Reddit agent is disabled.');

            return self::SUCCESS;
        }

        $this->info('Generating strategy report...');

        $command->execute();

        $this->info('Strategy report generated.');

        return self::SUCCESS;
    }
}
