<?php

declare(strict_types=1);

namespace App\Jobs\Broker\Deal;

use App\Mail\ErrorEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class ShareDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ShareDeal implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public $email;

    public $senderName;

    public $dollarAmount;

    public $loanType;

    public $url;

    public $existingAccount;

    public $lenderName;

    public $brokerCompany;

    public function __construct($email, $senderName, $dollarAmount, $loanType, $url, $existingAccount, $lenderName, $brokerCompany)
    {
        $this->email = $email;
        $this->senderName = $senderName;
        $this->dollarAmount = $dollarAmount;
        $this->loanType = $loanType;
        $this->url = $url;
        $this->existingAccount = $existingAccount;
        $this->lenderName = $lenderName;
        $this->brokerCompany = $brokerCompany;
    }

    public function handle()
    {
        if ($this->existingAccount) {
            try {
                Mail::to($this->email)->send(new \App\Mail\ShareDealRegisteredUsers($this->senderName, $this->dollarAmount, $this->loanType, $this->url, $this->existingAccount, $this->lenderName));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($this->email, ' A broker tried to share a deal directly to a lender(s)', $exception));
            }
        } elseif (! $this->existingAccount) {
            try {
                Mail::to($this->email)->send(new \App\Mail\ShareDeal($this->senderName, $this->dollarAmount, $this->loanType, $this->url, $this->existingAccount, $this->brokerCompany));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($this->email, ' A broker tried to share a deal directly to a lender(s)', $exception));
            }
        }
    }
}
