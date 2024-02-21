<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\MarkSubscriptionsAsCanceledCommand;
use App\Console\Commands\UpdateUserResponseTime;
use App\Jobs\Broker\Quote\ChooseQuoteBroker;
use App\Jobs\Broker\Quote\ChooseQuoteSecond;
use App\Jobs\Lender\Deal\DealSkipAutomatically;
use App\Jobs\Lender\Deal\FinishSecondStep;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\TestDealNotifyLenders::class,
        \App\Console\Commands\TestingCreateDealFromOtherDeal::class,
        \App\Console\Commands\TestDealandLenderQueries::class,
        \App\Console\Commands\UpdateUserResponseTime::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new FinishSecondStep())->everyMinute();
        $schedule->job(new ChooseQuoteBroker())->everyMinute();
        $schedule->job(new ChooseQuoteSecond())->everyMinute();
        $schedule->command(UpdateUserResponseTime::class)->daily();
        // $schedule->command(MarkSubscriptionsAsCanceledCommand::class)->everyMinute();

        if (config('app.run_deal_skip_job')) {
            $schedule->job(new DealSkipAutomatically())->everyMinute();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
