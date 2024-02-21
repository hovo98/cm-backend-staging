<?php

namespace App\Console\Commands;

use App\DataLog;
use App\User;
use Exception;
use Illuminate\Console\Command;

class ChangeEmailsToFakeForTesting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testing:change-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changing the emails for tesitng on staging.';

    public $whiteListedEmails = [
        'baila@everestequity.com',
    ];

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
        $this->sanityCheck();

        $users = User::where(function ($query) {
            $query->where('email', 'NOT LIKE', '%@64robots.com')
                ->where('email', 'NOT LIKE', '%@financelobby.com')
                ->whereNotIn('email', $this->whiteListedEmails);
        })
            ->get();

        $count = 1;
        foreach ($users as $user) {
            $this->swipeEmail($user, $count);
            $count++;
        }

        return 0;
    }

    public function swipeEmail(User $user, int $count)
    {
        $originalEmail = $user->email;

        $user->update(['email' => 'jon+fl'.$count.'@64robots.com']);

        DataLog::recordForModel($user, 'email-change', $originalEmail, [
            'original' => $originalEmail,
            'new' => $user->email,
        ]);
    }

    public function sanityCheck()
    {
        if (config('app.env') === 'production') {
            throw new Exception('Cannot run change email command on production');
        }

        if (! config('mail.staging-can-change-emails')) {
            throw new Exception('Staging variable not set');
        }
    }
}
