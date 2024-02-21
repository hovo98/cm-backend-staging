<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ErrorEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $email;

    protected $message;

    protected $exception;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $message, $exception)
    {
        $this->email = $email;
        $this->message = $message;
        $this->exception = $exception;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emails = $this->getEmails();

        return $this->from('no-reply@financelobby.com', 'Finance Lobby')
                    ->to($emails)
                    ->subject('Alert: Email(s) failed - '.config('app.app_check_env'))
                    ->view('mail.alertEmail', [
                        'emailString' => $this->email,
                        'messageString' => $this->message,
                        'exception' => $this->exception,
                        'year' => date('Y'),
                    ]);
    }

    public function getEmails(): array
    {
        if (config('app.app_check_env') === 'beta') {
            return [
                'chaim@financelobby.com',
                'jon@64robots.com',
                'michael@64robots.com',
                'miguel@64robots.com',
            ];
        }

        return [
            'jon@64robots.com',
            'miguel@64robots.com',
            'michael@64robots.com',
        ];
    }
}
