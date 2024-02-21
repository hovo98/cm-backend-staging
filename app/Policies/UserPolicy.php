<?php

namespace App\Policies;

use App\Exceptions\AccountTemporarilyLockedException;
use App\Exceptions\EmailUnverifiedException;
use App\Exceptions\InvalidCredentialsException;
use App\User;
use GraphQL\Error\Error;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function isLender(?User $user = null): bool
    {
        if (! $user || $user->role !== 'lender') {
            throw new AuthorizationException('You are not Lender.');
        }

        return true;
    }

    public function isBrokerAndBetaUser(?User $user = null): bool
    {
        return $this->isBroker($user) && $this->isBetaUser($user);
    }

    public function isBetaUser(?User $user = null): bool
    {
        if (! $user) {
            abort(403, 'Incorrect username or password');
        }

        $allowedUsers = User::allowedUsersBeta($user->email);
        if (! $allowedUsers) {
            abort(403, 'We\'re sorry, login temporarily limited to BETA users.');
        }

        return true;
    }

    public function isBroker(?User $user = null): bool
    {
        if (! $user || $user->getAttribute('role') !== 'broker') {
            throw new AuthorizationException('You are not Broker.');
        }

        return true;
    }

    public function isVerified(?User $user = null): bool
    {
        // Get email from query
        $query = request()?->input('query');
        $queryArray = explode(':', $query);
        $userEmail = str_replace('"', '', explode('password', $queryArray[2])[0]);
        $email = strtolower(str_replace(',', '', $userEmail));

        // Get user
        $theUser = User::where('email', trim($email))->first();

        // The messages from the exceptions are not being used.
        // Error messages shown to the SPA for 403 status responses
        // are being set up in this class: \App\Http\Middleware\AttemptAuthentication
        if (! $theUser) {
            $trashedUser = User::where('email', trim($email))->onlyTrashed()->first();

            // Check if user is blocked by admin
            if ($trashedUser) {
                throw new AccountTemporarilyLockedException('We\'re sorry, but this account was temporarily locked. Please refer to the email we sent you - we\'ll work with you to fix it ASAP.', 403);
            }

            throw new InvalidCredentialsException('Incorrect username or password', 403);
        }

        if ($theUser instanceof MustVerifyEmail && ! $theUser->hasVerifiedEmail()) {
            throw new EmailUnverifiedException('Please verify your email', 403);
        }

        return true;
    }
    public function validRecaptcha(?User $user = null): bool
    {
        if (app()->environment('testing', 'local')) {
            return true;
        }

        $query = request()?->input('query', '');

        $check_recaptcha = $this->verifyRecaptcha($this->extractRecaptcha($query));

        if (! $check_recaptcha) {
            throw new Error('An error occured, please try again');
        }

        return true;
    }

    /**
     * @param  string  $user_token
     * @return bool
     */
    private function verifyRecaptcha(string $user_token): bool
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = ['secret' => config('app.recaptcha.secret_key'), 'response' => $user_token];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);
        $response_decoded = json_decode($response, true);

        return ! empty($response_decoded['success']);
    }

    /**
     * Extract recaptcha token from GraphQL query
     *
     * @param  string  $query
     * @return string
     */
    private function extractRecaptcha(string $query): string
    {
        $re = '/recaptcha:?\s?"([\w\d-]*)"/m';

        preg_match($re, $query, $matches);

        return $matches[1] ?? '';
    }
}
