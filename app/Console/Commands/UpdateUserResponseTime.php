<?php

namespace App\Console\Commands;

use App\Jobs\SetUserResponseTime;
use App\User;
use Illuminate\Console\Command;

class UpdateUserResponseTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:set-response-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates user response time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        User::query()
            ->whereNull('deleted_at')
            ->chunkById(200, fn ($users) =>  SetUserResponseTime::dispatch($users));

        return Command::SUCCESS;
    }
}
