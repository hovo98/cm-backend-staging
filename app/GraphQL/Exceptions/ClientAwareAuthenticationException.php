<?php

namespace App\GraphQL\Exceptions;

use GraphQL\Error\ClientAware;
use Illuminate\Auth\AuthenticationException;

class ClientAwareAuthenticationException extends AuthenticationException implements ClientAware
{
    public function isClientSafe()
    {
        return true;
    }

    public function getCategory()
    {
        return 'unauthenticated';
    }
}
