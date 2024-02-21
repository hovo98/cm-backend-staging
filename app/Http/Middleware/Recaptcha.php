<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use GraphQL\Error\Error;
use Illuminate\Http\Request;

/**
 * Class Recaptcha
 */
class Recaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     *
     * @throws \GraphQL\Error\Error
     */
    public function handle(Request $request, Closure $next)
    {
        $query = $request->input('query', '');

        $check_recaptcha = $this->verifyRecaptcha($this->extractRecaptcha($query));

        if (! $check_recaptcha) {
            throw new Error('An error occured, please try again');
        }

        return $next($request);
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
