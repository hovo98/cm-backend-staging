<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\DataTransferObjects\SubscriptionMapper;
use App\Jobs\LogUserOutFromOtherDevices;
use App\Lender;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\Login;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class LoginUser extends Login
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null  $context
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $tempToken = '';
        $showTypeOfLoans = false;
        $response = parent::resolve($rootValue, $args, $context = null, $resolveInfo);
        $user = $response['user'];

        LogUserOutFromOtherDevices::dispatch($user);

        if ($user->role === 'lender') {
            $tempToken = $this->checkToken($user);
            $showTypeOfLoans = $this->checkTypeOfLoans($user);
        }

        $allowedUsers = User::allowedUsersBeta(trim($user->email));
        $nonBetaUser = ! $allowedUsers;

        if($user->role === 'broker') {
            $response['user']['plan'] = SubscriptionMapper::map($user->activePlan());
        }

        return array_merge(
            $response,
            [
                'tempToken' => $tempToken,
                'show_type_of_loans' => $showTypeOfLoans,
                'non_beta_user' => $nonBetaUser,
            ]
        );
    }

    private function checkToken($user): string
    {
        $lender = Lender::find($user->id);
        $preferences = $lender->getPerfectFit();
        $tempToken = '';

        if (! $preferences) {
            $tempToken = encrypt([
                'id' => $user->id,
                'email' => $user->email,
            ]);
        }

        return $tempToken;
    }

    /**
     * @param $user
     * @return bool
     */
    private function checkTypeOfLoans($user): bool
    {
        $typeOfLoans = true;
        $lender = Lender::find($user->id);
        $preferences = $lender->getPerfectFit();

        if (! $preferences) {
            return false;
        }
        $getTypeOfLoans = $preferences->getTypeOfLoansLender();
        if (! empty($getTypeOfLoans)) {
            $typeOfLoans = false;
        }

        return $typeOfLoans;
    }
}
