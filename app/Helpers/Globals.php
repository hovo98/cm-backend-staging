<?php

declare(strict_types=1);

use App\DataLog;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Return a new response from the application.
 *
 * @param  View|string|array|null  $content
 * @param  int  $status
 * @param  array  $headers
 * @return Response|ResponseFactory
 */
if (!function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        /** @var ResponseFactory $factory */
        $factory = app(ResponseFactory::class);

        if (func_num_args() === 0) {
            return $factory;
        }

        // If the request has Unauthenticated error, set status code to 401
        if (is_array($content) &&
            isset($content['errors'][0]['debugMessage']) &&
            in_array($content['errors'][0]['debugMessage'], ['Unauthenticated.', 'An error occured, refresh the page and try again'])) {
            $status = 401;
            DataLog::recordSimple('logging-response', '401-sent');
        }

        DataLog::recordSimple('logging-response', 'We got here');

        return $factory->make($content, $status, $headers);
    }
}
if (!function_exists('is_serialized')) {
    function is_serialized($data, $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                // Or else fall through.
                // no break
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';

                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }

        return false;
    }
}

/*
 * Run Invokable Class
 */
if (!function_exists('handle')) {
    function handle($invokableClass)
    {
        return $invokableClass();
    }
}
