<?php

namespace App\Console\Commands;

use App\Deal;
use App\Jobs\DealNotifyInterestedLenders;
use Illuminate\Console\Command;

class TestDealNotifyLenders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:deal-notify-lenders {dealId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary test command';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $deal = Deal::find($this->argument('dealId'));

        DealNotifyInterestedLenders::dispatchNow($deal);
    }
}
