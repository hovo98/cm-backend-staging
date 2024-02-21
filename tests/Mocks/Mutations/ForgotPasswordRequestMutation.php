<?php

namespace Tests\Mocks\Mutations;

use Illuminate\Support\Arr;

class ForgotPasswordRequestMutation
{
    public function __construct(protected array $attributes = [])
    {
    }

    public function __invoke()
    {
        $email = Arr::get($this->attributes, 'email', 'jon@64robots.com');

        return <<<FORGOTPASSWORDREQUESTMUTATION
mutation {
    forgotPassword(input: {
        email: "{$email}"
        recaptcha: "HFOXpwKgNXMXoTMHRFF1BUFhEATDooCgESPxNyZQR4Yz8jajw5QXEtT2UsOyBQeWVwTSdYXQkDEE9KCxgmEi8IK24SS3pVYnhsN3RzIRULAAZtMz1jR1kxZEx2PB4DSh4DNB0FbmZwNl4zHj5fHABeLmU8L2BgNFYEWTIxQxMnJm0UEg"
        }
    ) {
        status message
    } }
FORGOTPASSWORDREQUESTMUTATION;
    }
}
