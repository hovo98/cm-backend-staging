<?php

namespace App\Jobs;

use App\Mail\SendDealPreferenceFileMail;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MailDealPreferencesFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public string $filename, public User $user)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(20);

        $link = Storage::disk(config('filesystems.default'))
            ->temporaryUrl(
                $this->filename,
                now()->addDay(),
                [
                    'ResponseContentDisposition' => "attachment; filename=Lenders Deal Preferences.csv",
                ]
            );

        Mail::to($this->user->email)->send(new SendDealPreferenceFileMail($link));
    }
}
