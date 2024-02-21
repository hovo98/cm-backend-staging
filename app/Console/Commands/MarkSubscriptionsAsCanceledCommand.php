<?php

namespace App\Console\Commands;

use App\Jobs\MarkSubscriptionsAsCanceled;
use Illuminate\Console\Command;
use Laravel\Cashier\Subscription;

class MarkSubscriptionsAsCanceledCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:canceled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set subscription status to canceled';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Subscription::whereNotNull('ends_at')
            ->orWhereNotNull('trial_ends_at')
            ->chunkById(100, fn ($subscriptions) =>  MarkSubscriptionsAsCanceled::dispatch($subscriptions));

        return Command::SUCCESS;
    }
}
