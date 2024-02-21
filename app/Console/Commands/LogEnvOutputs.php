<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DataLog;

class LogEnvOutputs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:env-outputs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $envs = [
            'small_one_time_price' => env('STRIPE_SMALL_ONE_TIME_PRICE_ID'),

            'medium_one_time_price' => env('STRIPE_MEDIUM_ONE_TIME_PRICE_ID'),

            'large_one_time_price' => env('STRIPE_LARGE_ONE_TIME_PRICE_ID'),

            'extra_large_one_time_price' => env('STRIPE_EXTRA_LARGE_ONE_TIME_PRICE_ID'),
        ];

        $config = [
            'small_one_time_price' => config('stripe.small_one_time_price'),

            'medium_one_time_price' => config('stripe.medium_one_time_price'),

            'large_one_time_price' => config('stripe.large_one_time_price'),

            'extra_large_one_time_price' => config('stripe.extra_large_one_time_price'),
        ];

        DataLog::recordSimple('envs', 'direct-env', $envs);
        DataLog::recordSimple('envs', 'config-env', $config);


        return Command::SUCCESS;
    }
}
