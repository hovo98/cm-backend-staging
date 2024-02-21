<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Class InvitationEmailBrokerToBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class InvitationEmailBrokerToBroker extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $senderName;

    /**
     * @var string
     */
    public $senderFullName;

    /**
     * @var string|null
     */
    public $companyName;

    /**
     * @var string
     */
    public $currentYear;

    /**
     * Create a new message instance.
     *
     * @param  string  $senderName
     */
    public function __construct(User $user)
    {
        $activeCampaign = 'active+campaign';
        $this->url = config('app.frontend_url').'/sign-up?utm_source=' . $activeCampaign .'&utm_medium=email&utm_campaign=broker+to+broker+invite&invited_by='.$user->id;
        $this->senderName = $user->first_name;
        $this->senderFullName = $user->name();
        $this->company = $user->company?->company_name ?? '';
        $this->currentYear = date('Y');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->mailer(config('mail.alternative_mailer'))
            ->subject($this->senderName . ' sent you an invite to join Finance Lobby')
            ->view('mail.invitationEmailBrokerToBroker');
    }
}
