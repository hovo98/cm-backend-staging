<?php

namespace Tests\Feature;

use App\User;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function login_with_a_not_verified_email_user_results_in_403_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = null;
        $user->save();

        $loginMutation = <<<LOGINMUTATION
mutation {
    login(input: {
        username: "{$user->email}",
        password: "password"
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
LOGINMUTATION;

        $this->graphQL($loginMutation)->assertStatus(403)->assertJson([
            'errors' => [
                [
                    'extensions' => [
                        'reason' => 'Verify your account to get started.',
                        'show_resend' => true,
                    ]
                ]
            ]
        ]);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /** @test */
    public function login_with_invalid_email_credentials_results_in_403_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $loginMutation = <<<LOGININCORRECTEMAILMUTATION
mutation {
    login(input: {
        username: "not_existing_user@example.com",
        password: "password"
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

        $this->graphQL($loginMutation)
            ->assertStatus(403)
            ->assertJson([
                'errors' => [
                    [
                        'extensions' => [
                            'reason' => 'Incorrect username or password.',
                            'show_resend' => false,
                        ]
                    ]
                ]
            ]);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /** @test */
    public function login_with_invalid_password_credentials_results_in_403_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $loginMutation = <<<LOGININCORRECTEMAILMUTATION
mutation {
    login(input: {
        username: "{$user->email}",
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

        $this->graphQL($loginMutation)
            ->assertStatus(403)

            ->assertJson([
                'errors' => [
                    [
                        'extensions' => [
                            'reason' => 'Incorrect username or password.',
                            'show_resend' => false,
                        ]
                    ]
                ]
            ]);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /** @test */
    public function login_with_trashed_user_results_in_403_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->deleted_at = now();
        $user->save();

        $loginMutation = <<<LOGINTRASHEDUSERMUTATION
mutation {
    login(input: {
        username: "{$user->email}",
        password: "password"
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
LOGINTRASHEDUSERMUTATION;

        $this->graphQL($loginMutation)
            ->assertStatus(403)
            ->assertJson([
                'errors' => [
                    [
                        'extensions' => [
                            'reason' => 'We\'re sorry, but this account was temporarily locked. Please refer to the email we sent you - we\'ll work with you to fix it ASAP.',
                            'show_resend' => false,
                        ]
                    ]
                ]
            ]);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /** @test */
    public function login_with_correct_user_and_password_results_in_200_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $loginMutation = <<<CORRECTLOGINMUTATION
mutation {
    login(input: {
        username: "{$user->email}",
        password: "password"
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
            chat_response_time
            company {
                company_name
                domain
            }
        }
    }
}
CORRECTLOGINMUTATION;

        $this->graphQL($loginMutation)
            ->assertStatus(200);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /** @test  */
    public function returns_current_subscription_for_user()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create(['role' => 'broker']);
        $user->email_verified_at = now();
        $user->save();

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
            'user_id' => $user->id
        ]);

        $loginMutation = <<<CORRECTLOGINMUTATION
mutation {
    login(input: {
        username: "{$user->email}",
        password: "password"
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
            plan {
                stripe_id
            }
            company_id
            profile_image
            chat_response_time
            company {
                company_name
                domain
            }
        }
    }
}
CORRECTLOGINMUTATION;

        $this->graphQL($loginMutation)
            ->assertStatus(200);

        $this->resetPassportConfig($oldPassportConfig);
    }

    /**
     * @return void
     * @test
     */
    public function login_for_spark()
    {
        $user = User::factory()->create(['role' => 'broker']);
        $this
            ->actingAs($user, 'api')
            ->graphQL('
                mutation {
                    sparkLogin (
                        input: {
                                return_url: "http://google.com"
                            }
                       ) {
                        success
                    }
                }
            ')
            ->assertStatus(200);
    }

    /** @test */
    public function authorized_route_with_no_user_and_password_results_in_401_response()
    {
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $incorrectLogoutMutation = <<<INCORRECTLOGOUTMUTATION
mutation {
    logout {
        status
        message
    }
}
INCORRECTLOGOUTMUTATION;

        $this->graphQL($incorrectLogoutMutation)
            ->assertJson([
                'data' => null,
                'error' => 'Unauthenticated',
                'success' => false,
            ])
            ->assertStatus(401);

        $this->resetPassportConfig($oldPassportConfig);
    }
}
