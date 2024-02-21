<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Class ContactForm
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class ContactForm extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $contactMessage;

    /**
     * @var string
     */
    public $contactSubject;

    /**
     * @var string
     */
    public $currentYear;

    /**
     * Create a new message instance.
     *
     * @param  Collection  $args
     */
    public function __construct(Collection $args)
    {
        $this->firstName = $args->get('firstName', '');
        $this->lastName = $args->get('lastName', '');
        $this->email = $args->get('email', '');
        $this->contactSubject = $args->get('subject', '');
        $this->contactMessage = $args->get('message', '');
        $this->currentYear = date('Y');
        $this->to(explode(',', config('mail.to')));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): Mailable
    {
        return $this
            ->subject(sprintf('New message from %s %s', $this->firstName, $this->lastName))
            ->replyTo($this->email)
            ->view('mail.contactEmail');
    }
}
