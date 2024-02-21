<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use App\User;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class CheckEmail
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class CheckEmail
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     *
     * @throws Error
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($args && $args['email']) {
            $email = filter_var(trim($args['email']), FILTER_VALIDATE_EMAIL);

            if (is_bool($email)) {
                return [
                    'success' => false,
                    'message' => 'Please enter your email address in this format: yourname@example.com.',
                ];
            }

            $emailCheck = explode('@', $email);

            $forbidden = [
                'gmail.com',
                'yahoo.com',
                'yahoo.fr',
                'yahoo.co.uk',
                'yahoo.com.br',
                'yahoo.co.in',
                'yahoo.es',
                'yahoo.it',
                'yahoo.de',
                'yahoo.in',
                'yahoo.ca',
                'yahoo.co.jp',
                'yahoo.com.au',
                'yahoo.com.ar',
                'yahoo.com.mx',
                'yahoo.co.id',
                'yahoo.com.sg',
                'outlook.com',
                'aol.com',
                'hotmail.com',
                'hotmail.co.uk',
                'hotmail.fr',
                'hotmail.it',
                'hotmail.es',
                'hotmail.de',
                'icloud.com',
                'inbox.com',
                'mail.com',
                'zoho.com',
                'msn.com',
                'wanadoo.fr',
                'orange.fr',
                'comcast.net',
                'live.com',
                'live.co.uk',
                'live.fr',
                'live.nl',
                'live.it',
                'live.com.au',
                'rediffmail.com',
                'free.fr',
                'gmx.de',
                'web.de',
                'yandex.ru',
                'ymail.com',
                'libero.it',
                'uol.com.br',
                'bol.com.br',
                'mail.ru',
                'cox.net',
                'sbcglobal.net',
                'sfr.fr',
                'verizon.net',
                'googlemail.com',
                'ig.com.br',
                'bigpond.com',
                'terra.com.br',
                'neuf.fr',
                'alice.it',
                'rocketmail.com',
                'att.net',
                'laposte.net',
                'facebook.com',
                'bellsouth.net',
                'charter.net',
                'rambler.ru',
                'tiscali.it',
                'shaw.ca',
                'sky.com',
                'earthlink.net',
                'optonline.net',
                'freenet.de',
                't-online.de',
                'aliceadsl.fr',
                'virgilio.it',
                'home.nl',
                'qq.com',
                'telenet.be',
                'me.com',
                'tiscali.co.uk',
                'voila.fr',
                'gmx.net',
                'planet.nl',
                'tin.it',
                'ntlworld.com',
                'arcor.de',
                'frontiernet.net',
                'hetnet.nl',
                'zonnet.nl',
                'club-internet.fr',
                'juno.com',
                'optusnet.com.au',
                'blueyonder.co.uk',
                'bluewin.ch',
                'skynet.be',
                'sympatico.ca',
                'windstream.net',
                'mac.com',
                'centurytel.net',
                'chello.nl',
                'live.ca',
                'aim.com',
                'bigpond.net.au',
            ];

            if (in_array($emailCheck, $forbidden)) {
                throw new Error('Please enter a corporate email address.');
            }

            $role = User::userExistsByEmail($email);
            if ($role) {
                throw new Error('An account with this email address already exists. Did you mean to <a style="color: #055d64;transition: 350ms ease;
            font-weight: 700;border-bottom: none;" class="txt-with-link-be" href="/login">log in</a>?');
            }

            return [
                'success' => true,
                'message' => 'Email valid.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Email is missing',
        ];
    }
}
