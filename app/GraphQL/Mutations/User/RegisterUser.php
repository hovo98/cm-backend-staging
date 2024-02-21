<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Company;
use App\Events\CompanyChanged;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\Register;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RegisterUser
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class RegisterUser extends Register
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Joselfonseca\LighthouseGraphQLPassport\Exceptions\AuthenticationException
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $model = app(config('auth.providers.users.model'));

        $input = collect($args)->toArray();
        // Hash password
        $input['password'] = Hash::make($input['password']);

        // Get email
        $email = filter_var(trim($args['email']), FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);

        // Get domain from email
        $emailDomainOnly = preg_replace('/.+@/', '', $email);

        // Check if company exists
        $existsDomain = DB::table('companies')
            ->where('domain', $emailDomainOnly)
            ->first();

        // Check if Lender were invited by colleague
        $token_track_referral = isset($input['token_track_referral']) ? $input['token_track_referral'] : '';
        unset($input['token_track_referral']);

        if ($token_track_referral && $input['role'] === 'lender') {
            $payload = decrypt($token_track_referral);
            $user = User::whereId($payload['id'])->whereEmail($payload['email'])->first();
            // Check user role also and if user exists
            $input['referrer_id'] = $user && $user->role === 'lender' ? $user->id : null;
        }

        $invitedBy = data_get($args, 'invited_by');

        if ($invitedBy) {
            $user = User::find($invitedBy);
            // Check user role also and if user exists
            $input['referrer_id'] = $user ? $user->id : null;
        }

        // Create new User
        $model->fill($input);
        $model->site = User::BETA_SIGNUP;
        $model->save();

        // If exists add Company to User
        if ($existsDomain) {
            $model->update(['company_id' => $existsDomain->id]);
        } else {
            // Create Company
            $company = new Company();
            $company->fill(['domain' => $emailDomainOnly]);
            $company->fill(['company_status' => Company::PENDING_COMPANY_STATUS]);
            $company->save();

            // Add company to User
            $model->update(['company_id' => $company->id]);
            event(new CompanyChanged($company, 'createdByUser'));
        }

        $tempToken = '';

        //Check if Lender is invited by brokers and attach him to that brokers
        if ($input['role'] === 'lender') {
            $tempToken = encrypt([
                'id' => $model->id,
                'email' => $model->email,
            ]);

            // Get all connections
            $existsLenderEmail = DB::table('broker_lender_email')
                                ->where('email', $email)
                                ->get();

            if ($existsLenderEmail) {
                foreach ($existsLenderEmail as $key) {
                    // Find Broker
                    $broker = Broker::find($key->broker_id);
                    // Delete connection for that broker
                    $broker->lenderEmails()->wherePivot('email', $email)->detach();
                    // Attach Lender to Broker
                    $broker->lenders()->attach($model->id);
                }
            }
        }

        if ($model instanceof MustVerifyEmail) {
            event(new Registered($model));

            return [
                'token' => $tempToken,
                'status' => 'MUST_VERIFY_EMAIL',
            ];
        }

        // Build credentials
        $credentials = $this->buildCredentials([
            'username' => $args[config('lighthouse-graphql-passport.username')],
            'password' => $args['password'],
        ]);

        // Return tokens in response
        $response = $this->makeRequest($credentials);

        // Return User in response
        $response['user'] = $model;
        event(new Registered($model));

        return [
            'tokens' => $response,
            'status' => 'SUCCESS',
        ];
    }
}
