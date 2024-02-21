<?php

namespace App\Console\Commands;

use App\DataLog;
use App\Deal;
use App\Imports\ExpectedLendersNotifiedOfDeal;
use Illuminate\Console\Command;

class TestCrossReferenceNotifiedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cross-reference-notified-emails {dealId} {importFileName}';

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
        $deal = Deal::find($this->argument('dealId'));

        $expectedRecipients = (new ExpectedLendersNotifiedOfDeal())->toCollection($this->argument('importFileName'))
            ->first()->map(function ($row) {
                return $row['lenders_notified'];
            });

        $dealRecipients = $deal->emailNotifications->pluck('recipient_email');

        $shouldnHaveRecieved = $dealRecipients->reject(function ($email) use ($expectedRecipients) {
            return $expectedRecipients->contains($email);
        });

        $missedRecipients = $expectedRecipients->reject(function ($email) use ($dealRecipients) {
            return $dealRecipients->contains($email);
        });

        // logger(json_encode([
        //     'wrong_recipients' => $shouldnHaveRecieved,
        //     'missed_recipients' => $missedRecipients,
        // ]));

        DataLog::recordForModel($deal, 'testing', 'email-reconciliation', [
            'wrong_recipients_count' => $shouldnHaveRecieved->count(),
            'missed_recipients_count' => $missedRecipients->count(),
            'wrong_recipients' => $shouldnHaveRecieved->toArray(),
            'missed_recipients' => $missedRecipients->toArray(),
        ]);

        return 0;
    }
}
