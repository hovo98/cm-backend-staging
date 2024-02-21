<?php

namespace App\GraphQL\Execution;

use App\Exceptions\AccountTemporarilyLockedException;
use App\Exceptions\EmailUnverifiedException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\PaymentException;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\AuthenticationException as JoselfonsecaAuthenticationException;
use App\GraphQL\Exceptions\ClientAwareAccountTemporarilyLockedException;
use App\GraphQL\Exceptions\ClientAwareAuthenticationException;
use App\GraphQL\Exceptions\ClientAwareEmailUnverifiedException;
use App\GraphQL\Exceptions\ClientAwareInvalidCredentialsException;
use Closure;
use GraphQL\Error\Error;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\EmailNotSentException;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;
use Nuwave\Lighthouse\Execution\ExtensionErrorHandler;
use Illuminate\Auth\AuthenticationException;

/**
 * Handle Exceptions that implement Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions
 * and add extra content from them to the 'extensions' key of the Error that is rendered
 * to the User.
 */
class CustomExtensionErrorHandler extends ExtensionErrorHandler
{
    public function __invoke(?Error $error, Closure $next): array
    {
        if (null === $error) {
            return $next(null);
        }

        $underlyingException = $error->getPrevious();

        if ($underlyingException instanceof  PaymentException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath()
            );

            return $next($error);
        }

        if ($underlyingException instanceof EmailUnverifiedException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new ClientAwareEmailUnverifiedException(
                    $error->getPrevious()?->getMessage(),
                )
            );

            return $next($error);
        }

        // Incorrect Email
        if ($underlyingException instanceof InvalidCredentialsException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new ClientAwareInvalidCredentialsException(
                    $error->getPrevious()?->getMessage(),
                )
            );

            return $next($error);
        }

        // Incorrect Password
        $paths = $error->getPath();
        $path = $paths[0] ?? null;
        if ($path === 'login' && $underlyingException instanceof JoselfonsecaAuthenticationException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new ClientAwareInvalidCredentialsException(
                    $error->getPrevious()?->getMessage(),
                )
            );

            return $next($error);
        }

        if ($underlyingException instanceof AccountTemporarilyLockedException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new ClientAwareAccountTemporarilyLockedException(
                    $error->getPrevious()?->getMessage(),
                )
            );

            return $next($error);
        }

        if ($underlyingException instanceof EmailNotSentException) {
            $error = new Error(
                "We can't find a user with that email address.",
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $underlyingException,
                $underlyingException->extensionsContent()
            );

            return $next($error);
        }

        if ($underlyingException instanceof RendersErrorsExtensions) {
            // Reconstruct the error, passing in the extensions of the underlying exception
            $error = new Error( // @phpstan-ignore-line TODO remove after graphql-php upgrade
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $underlyingException,
                $underlyingException->extensionsContent()
            );

            return $next($error);
        }

        if ($error->getPrevious() instanceof AuthenticationException) {
            $error = new Error(
                $error->getMessage(),
                $error->getNodes(),
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new ClientAwareAuthenticationException(
                    $error->getPrevious()->getMessage(),
                    $error->getPrevious()->guards(),
                    $error->getPrevious()->redirectTo()
                )
            );

            return $next($error);
        }

        return $next($error);
    }
}
