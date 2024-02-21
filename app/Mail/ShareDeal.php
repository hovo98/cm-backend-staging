<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class ShareDeal extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $senderName;

    public $dollarAmount;

    public $loanType;

    public $url;

    public $existingAccount;

    public $currentYear;

    public $brokerCompany;

    /**
     * Create a new message instance.
     *
     * @param $senderName
     * @param $dollarAmount
     * @param $loanType
     * @param $url
     * @param $existingAccount
     * @param $brokerCompany
     */
    public function __construct($senderName, $dollarAmount, $loanType, $url, $existingAccount, $brokerCompany)
    {
        $this->senderName = $senderName;
        $this->dollarAmount = $dollarAmount;
        $this->loanType = $loanType;
        $this->url = $url;
        $this->existingAccount = $existingAccount;
        $this->currentYear = date('Y');
        $this->brokerCompany = $brokerCompany;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->getSubjectForEmail())
                    ->view('mail.shareDeal');
    }

    private function getSubjectForEmail(): string
    {
        if ($this->brokerCompany) {
            return $this->senderName.' from '.$this->brokerCompany.' shared a deal with you';
        }

        return $this->senderName.' shared a deal with you';
    }
}
