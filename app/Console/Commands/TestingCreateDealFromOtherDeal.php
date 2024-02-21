<?php

namespace App\Console\Commands;

use App\Deal;
use Illuminate\Console\Command;

class TestingCreateDealFromOtherDeal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing:create-deal-from-other-deal {dealId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        if (! in_array(config('app.env'), ['local', 'staging'])) {
            throw new \Exception('You can only runt his on local or staging', 1);
        }

        $deal = Deal::find($this->argument('dealId'));

        Deal::forceCreate([
            'user_id' => $deal->user_id,
            'data' => $deal->data,
            'lastStepStatus' => $deal->lastStepStatus,
            'termsheet' => $deal->termsheet,
            'dollar_amount' => $deal->dollar_amount,
            'location' => $deal->location,
            'sponsor_name' => $deal->sponsor_name,
            'unseen_quotes' => false,
            'currently_editing' => false,
        ]);

        return 0;
    }
}
