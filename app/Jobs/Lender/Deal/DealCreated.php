<?php

declare(strict_types=1);

namespace App\Jobs\Lender\Deal;

use App\DataLog;
use App\Deal;
use App\DealEmail;
use App\EmailNotification;
use App\Lender;
use App\Mail\ErrorEmail;
use App\Notifications\DealCreated as DealCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class DealCreated
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DealCreated implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Lender $lender;

    public Deal $deal;

    public $dealMapped;

    public $connected;

    public $inc;

    public $count;

    public $arrOfEmailLender;

    public $flag;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    public function __construct(Lender $lender, array $dealMapped, bool $connected, $inc, $count, $arrOfEmailLender, $flag = '')
    {
        $this->onQueue('emails');

        $this->lender = $lender;
        $this->deal = Deal::find($dealMapped['id']);
        $this->dealMapped = $dealMapped;
        $this->connected = $connected;
        $this->inc = $inc;
        $this->count = $count;
        $this->arrOfEmailLender = $arrOfEmailLender;
        $this->flag = $flag;

        // save deal and lenders email to db
        if ($lender->beta_user) {
            $dealEmail = new DealEmail();
            $dealEmail->deal_id = $dealMapped['id'];
            $dealEmail->email = $lender->email;
            $dealEmail->save();
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [];
    }

    public function handle()
    {
        $sentEmail = EmailNotification::makeOne(
            sentBy: $this->deal->broker,
            recipient: $this->lender,
            reference: $this->deal,
            mailable: DealCreatedNotification::class,
            params: [
                $this->dealMapped,
                $this->connected,
            ],
        );

        DataLog::recordForModel($this->lender, 'log-connection-type', $this->flag);

        try {
            $notification = new DealCreatedNotification($this->dealMapped, $this->connected);
            $this->lender->notify($notification);
        } catch (\Throwable $exception) {
            $sentEmail->setFailed($exception->getMessage());

            DataLog::recordForModel($this->lender, 'error', 'mail-send', null, $exception->getMessage());

            if ($this->inc === $this->count) {
                Mail::send(new ErrorEmail(implode(', ', $this->arrOfEmailLender), 'Send email to lenders when deal published, deal skipped or Lenders colleague', $exception));
            }
        }
    }
}
