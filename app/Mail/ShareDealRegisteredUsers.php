<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ShareDealRegisteredUsers
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ShareDealRegisteredUsers extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $senderName;

    public $dollarAmount;

    public $loanType;

    public $url;

    public $existingAccount;

    public $currentYear;

    public $lenderName;

    /**
     * Create a new message instance.
     *
     * @param $senderName
     * @param $dollarAmount
     * @param $loanType
     * @param $url
     * @param $existingAccount
     * @param $lenderName
     */
    public function __construct($senderName, $dollarAmount, $loanType, $url, $existingAccount, $lenderName)
    {
        $this->senderName = $senderName;
        $this->dollarAmount = $dollarAmount;
        $this->loanType = $loanType;
        $this->url = $url;
        $this->existingAccount = $existingAccount;
        $this->currentYear = date('Y');
        $this->lenderName = $lenderName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->senderName.' has shared a new deal with you')
            ->view('mail.shareDealRegisteredUsers');
    }
}
