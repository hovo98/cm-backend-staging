<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Mail\ContactForm as ContactFormEmail;
use App\Mail\ErrorEmail;
use Illuminate\Support\Facades\Mail;

/**
 * Class DealForm
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class ContactForm
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @return array
     */
    public function resolve($rootValue, array $args): array
    {
        $args = collect($args)->except('recaptcha');

        try {
            Mail::send(new ContactFormEmail($args));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($args->get('email', ''), ' Contact Finance Lobby email ', $exception));
        }

        return [
            'message' => implode(', ', Mail::failures()) ?: 'Email sent',
            'success' => empty(Mail::failures()),
        ];
    }
}
