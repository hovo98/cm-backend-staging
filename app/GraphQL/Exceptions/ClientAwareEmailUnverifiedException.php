<?php

namespace App\GraphQL\Exceptions;

use GraphQL\Error\ClientAware;
use Nuwave\Lighthouse\Exceptions\GenericException;

class ClientAwareEmailUnverifiedException extends GenericException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return 'email-unverified';
    }
}
