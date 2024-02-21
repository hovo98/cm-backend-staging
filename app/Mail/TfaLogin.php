<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TfaLogin extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $user;

    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
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
                    ->from('no-reply@financelobby.com', 'Finance Lobby')
                    ->to($this->user->email)
                    ->subject('Two Factor Authentication')
                    ->view('mail.tfa', [
                        'user' => $this->user,
                        'token' => $this->token,
                        'year' => '2022',
                    ]);
    }
}
