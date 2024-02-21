<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\AuthenticationException;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\RefreshToken as RefreshTokenJoselfonseca;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Throwable;

/**
 * Class RefreshToken
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class RefreshToken extends RefreshTokenJoselfonseca
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $credentials = $this->buildCredentials($args, 'refresh_token');

        try {
            return $this->makeRequest($credentials);
        } catch (Throwable $e) {
            throw new AuthenticationException('An error occured, refresh the page and try again');
        }
    }
}
