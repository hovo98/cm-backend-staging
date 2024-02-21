<?php

namespace App\Notifications;

use App\DataTransferObjects\Fit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class WelcomeEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\DataTransferObjects\Fit
     */
    protected $deal_preferences;

    /**
     * ApproveCompany constructor.
     *
     * @param  \App\DataTransferObjects\Fit  $deal_preferences
     */
    public function __construct(Fit $deal_preferences)
    {
        $this->queue = 'emails';
        $this->deal_preferences = $deal_preferences;
    }

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
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
        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.welcomeEmail';

        $mailMessage->viewData = [
            'user' => $notifiable->first_name,
            'areas' => $this->deal_preferences->getAreas(),
            'loanRange' => $this->deal_preferences->getLoanSize(),
            'assetTypes' => $this->deal_preferences->getAssetTypesNames(),
            'multifamily' => $this->deal_preferences->getMultifamily(),
            'otherAssetTypes' => $this->getOtherAssets($this->deal_preferences->getOtherAssetTypesNames()),
            'bifurcatedAssets' => $this->getOptionsOtherAssets($this->deal_preferences->getOtherAssetTypesNames()),
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Welcome to Finance Lobby, '.$notifiable->first_name));
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }

    /**
     * @param $otherAssetTypesNames
     * @return mixed
     */
    private function getOtherAssets($otherAssetTypesNames)
    {
        if (array_key_exists(6, $otherAssetTypesNames)) {
            unset($otherAssetTypesNames[6]);
        }
        if (array_key_exists(7, $otherAssetTypesNames)) {
            unset($otherAssetTypesNames[7]);
        }

        return $otherAssetTypesNames;
    }

    /**
     * @param $otherOptionAssetTypesNames
     * @return string
     */
    private function getOptionsOtherAssets($otherOptionAssetTypesNames): string
    {
        $bifurcatedAssets = '';

        if (array_key_exists(6, $otherOptionAssetTypesNames)) {
            $bifurcatedAssets .= $otherOptionAssetTypesNames[6];
        }
        if (array_key_exists(6, $otherOptionAssetTypesNames) && array_key_exists(7, $otherOptionAssetTypesNames)) {
            $bifurcatedAssets .= '/';
        }
        if (array_key_exists(7, $otherOptionAssetTypesNames)) {
            $bifurcatedAssets .= $otherOptionAssetTypesNames[7];
        }

        return $bifurcatedAssets;
    }
}
