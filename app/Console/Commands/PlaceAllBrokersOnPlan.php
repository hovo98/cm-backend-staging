<?php

namespace App\Console\Commands;

use App\Jobs\SubBrokersToPlan;
use App\User;
use Illuminate\Console\Command;

class PlaceAllBrokersOnPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brokers:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Place users on 15 days free trail';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        User::where('role', 'broker')
            ->whereNull('deleted_at')
            ->chunkById(200, fn ($users) =>  SubBrokersToPlan::dispatch($users));

        return Command::SUCCESS;
    }
}
