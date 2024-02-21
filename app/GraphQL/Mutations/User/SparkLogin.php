<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\LoginRedirects;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\URL;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SparkLogin
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {

        $url = URL::temporarySignedRoute('subscribe', now()->addMinutes(5), [
            'user' => $context->user()
        ]);

        LoginRedirects::create([
            'user_id' => $context->user()->id,
            'url' => $args['return_url']
        ]);

        return  [
            'success' =>  $url
        ];
    }
}
