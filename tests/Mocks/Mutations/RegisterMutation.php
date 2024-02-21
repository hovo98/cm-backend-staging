<?php

namespace Tests\Mocks\Mutations;

use Illuminate\Support\Arr;

class RegisterMutation
{
    public function __construct(protected array $attributes = [])
    {
    }

    public function __invoke()
    {
        $email = Arr::get($this->attributes, 'email', 'jon@64robots.com');

        return <<<REGISTERMUTATION
mutation {
    register(input: {
        role: "lender",
        first_name: "John",
        last_name: "Doe",
        email: "{$email}"
        password: "Mor3Th4n4Pa%%w0rd"
        recaptcha: "1234567890"
        }
    ) {
        token
        status
    }
}
REGISTERMUTATION;
    }
}
