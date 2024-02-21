<?php

namespace Tests\Mocks\Mutations;

use Illuminate\Support\Arr;

class LoginInvalidPasswordMutation
{
    public function __construct(protected array $attributes = [])
    {
    }

    public function __invoke()
    {
        $email = Arr::get($this->attributes, 'email', 'jon@64robots.com');

        return <<<LOGININCORRECTEMAILMUTATION
mutation {
    login(input: {
        username: "{$email}",
        password: "a_not_existent_password"
        }
    ) {
        user {
            role
            id
            first_name
            last_name
            email
            subscription
            status
            company_id
            profile_image
            company {
                company_name
                domain
            }
        }
    }
}
LOGININCORRECTEMAILMUTATION;
    }
}
