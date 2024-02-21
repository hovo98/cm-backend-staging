<?php

namespace Tests\Feature;

use App\Notifications\ResetPassword;
use App\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\Mutations\ForgotPasswordRequestMutation;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /***************************************************************************************
     ** GET
     ***************************************************************************************/

    /**
     * @test
     * POST '/graphql'
     */
    public function the_system_can_generate_a_password_reset_email_for_a_user()
    {
        Notification::fake();

        $user = User::factory()->broker()->emailVerified()->create();

        $this->graphQL(handle(new ForgotPasswordRequestMutation(['email' => $user->email])))
            ->assertStatus(200);

        Notification::assertSentTo([$user], ResetPassword::class);
    }


    /**
     * @test
     * POST '/graphql'
     */
    public function the_system_can_generate_a_proper_error_for_a_not_existent_user_when_a_password_reset_is_requested()
    {
        Notification::fake();

        $this->graphQL(handle(new ForgotPasswordRequestMutation(['email' => 'notexistent@example.com'])))
            ->assertOk()
            ->assertGraphQLError(new Error("We can't find a user with that email address."));

        Notification::assertNothingSent();
    }
}
