<?php

namespace App\Notifications;

use App\AssetTypes;
use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

/**
 * Class SendChatMessage
 */
class SendChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;

    private $room_id;

    private $deal_id;

    private $room;

    private $count;

    private $companyName;

    public function __construct($user, $room_id, $deal_id, $room, $count, $companyName)
    {
        $this->user = $user;
        $this->room_id = $room_id;
        $this->deal_id = $deal_id;
        $this->room = $room;
        $this->count = $count;
        $this->companyName = $companyName;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl();

        $mapper = new DealMapper($this->deal_id);
        $deal = $mapper->mapFromEloquent();

        $assetTypeId = $deal['inducted']['property_type']['asset_types'];
        (count($assetTypeId) > 1 && $assetTypeId[0] === 5)
            ? $title = AssetTypes::find($assetTypeId[1])->title
            : $title = AssetTypes::find($assetTypeId[0])->title;

        Log::info($title);

        if ($this->user->role === 'broker') {
            $location = $deal['location']['street_address'].', '.$deal['location']['city'].', '.$deal['location']['state'].', '.$deal['location']['zip_code'];
            $subject = 'New message re: loan request for '.$location;
        } else {
            $subject = $this->companyName.' responded to your message';
            $deal['show_address'] = $this->checkShowAddress($deal);
            ($deal['show_address'])
                ? $location = $deal['location']['street_address'].', '.$deal['location']['city'].', '.$deal['location']['state'].', '.$deal['location']['zip_code']
                : $location = $deal['location']['city'].', '.$deal['location']['state'].', '.$deal['location']['zip_code'];
        }

        $mailMessage = new MailMessage();

        $longMessage = $this->getLastMessage();

        $mailMessage->view = 'mail.newChatMessage';
        $mailMessage->viewData = [
            'user' => $this->user,
            'role' => $this->user->role,
            'url' => $verificationUrl,
            'lastMessage' => strlen($longMessage) > 50 ? substr($longMessage, 0, 50).'...' : $longMessage,
            'countMsg' => $this->count,
            'amount' => $this->formatDollarAmountDeal($deal),
            'location' => $location,
            'type' => ucfirst(strtolower(Deal::LOAN_TYPE[$deal['loan_type']])),
            'assetType' => $title,
            'year' => date('Y'),
            'companyName' => $this->companyName,
        ];

        $mailMessage->bcc = null;
        $mailMessage->from('no-reply@financelobby.com', 'Finance Lobby');

        return $mailMessage->subject(Lang::get($subject));
    }

    private function verificationUrl()
    {
        if ($this->user->role === 'lender') {
            return config('app.frontend_url').'/individual-deal/'.$this->deal_id.'?room='.$this->room;
        } else {
            return config('app.frontend_url').'/individual-deal-broker/'.$this->deal_id.'?room='.$this->room;
        }
    }

    private function formatDollarAmountDeal($deal): string
    {
        $amount = $deal['inducted']['loan_amount'];
        if ($amount !== 0 || $amount !== '0') {
            $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
            $amountDollar = $fmt->formatCurrency((int) $amount, 'USD');
            $formatAmount = str_replace('.00', '', $amountDollar);
            if ($formatAmount === '$0') {
                return '';
            }

            return $formatAmount;
        }

        return '';
    }

    private function getLastMessage(): string
    {
        $msg = Message::select()
        ->where('room_id', $this->room_id)
        ->whereNotIn('id', [$this->user->id])
        ->where('seen', false)
        ->orderBy('created_at', 'desc')->first();

        if (! $msg) {
            return '';
        }

        return $msg['message'];
    }

    private function checkShowAddress($mappedDeal): bool
    {
        $loan_type = $mappedDeal['inducted']['loan_type'];

        if ($loan_type === 1) {
            return $mappedDeal['show_address_purchase'] === 'true' ? false : true;
        } elseif ($loan_type === 3) {
            return $mappedDeal['construction_loan']['show_address_construction'] === 'true' ? false : true;
        } else {
            return true;
        }
    }
}
