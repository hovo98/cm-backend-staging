<?php

namespace App\Enums;

enum OAuthExceptionCode: int
{
    // Referenced In: League\OAuth2\Server\Exception\OAuthServerException
    case UNSUPPORTED_GRANT_TYPE = 2;
    case INVALID_REQUEST = 3;
    case CLIENT_AUTH_FAILED = 4;
    case INVALID_SCOPE = 5;
    case INVALID_CREDENTIALS = 6;
    case SERVER_ERROR = 7;
    case INVALID_REFRESH_TOKEN = 8;
    case ACCESS_DENIED = 9;
    case INVALID_GRANT = 10;

    public function title(): string
    {
        return match ($this) {
            self::UNSUPPORTED_GRANT_TYPE => 'Unsupported Grant Type',
            self::INVALID_REQUEST => 'Invalid Request',
            self::CLIENT_AUTH_FAILED => 'Client Auenthication Failed',
            self::INVALID_SCOPE => 'Invalid Scope',
            self::INVALID_CREDENTIALS => 'Invalid Credentials',
            self::SERVER_ERROR => 'Server Error',
            self::INVALID_REFRESH_TOKEN => 'Invalid Refesh Token',
            self::ACCESS_DENIED => 'Access Denied',
            self::INVALID_GRANT => 'Invalid Grant',
        };
    }

    public function httpStatusCode(): int
    {
        return match ($this) {
            self::UNSUPPORTED_GRANT_TYPE => 400,
            self::INVALID_REQUEST => 400,
            self::CLIENT_AUTH_FAILED => 400,
            self::INVALID_SCOPE => 400,
            self::INVALID_CREDENTIALS => 400,
            self::SERVER_ERROR => 500,
            self::INVALID_REFRESH_TOKEN => 401,
            self::INVALID_CREDENTIALS => 400,
            self::ACCESS_DENIED => 401,
            self::INVALID_GRANT => 400,
        };
    }

    /**
     * Dont Report To Sentry
     */
    public function shouldntReport(): bool
    {
        return match ($this) {
            self::INVALID_REFRESH_TOKEN => true,
            self::INVALID_CREDENTIALS => true,
            default => false,
        };
    }

    public static function dropdown(): array
    {
        $cases = self::cases();

        return collect($cases)->mapWithKeys(function ($condition) {
            return [$condition->value => $condition->title()];
        })
            ->toArray();
    }
}
