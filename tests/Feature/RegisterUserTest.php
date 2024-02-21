<?php

namespace Tests\Feature;

use App\EmailLog;
use App\Lender;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Mocks\Mutations\RegisterMutation;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_an_user_only_with_valid_email()
    {
        $email = "あいうえお@domain.com";

        $registerMutation = <<<REGISTERMUTATION
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

        $response = $this->graphQL($registerMutation)
            ->assertStatus(200);

        $this->assertEquals('MUST_VERIFY_EMAIL', $response->json('data.register.status'));
    }

    /**
     * @return void
     * @test
     */
    public function it_can_track_user_referrals(): void
    {
        $email = "johndoe@domain.com";

        $lender = Lender::factory()->create();

        $registerMutation = <<<REGISTERMUTATION
                mutation {
                    register(input: {
                        role: "lender",
                        first_name: "John",
                        last_name: "Doe",
                        email: "{$email}"
                        password: "Mor3Th4n4Pa%%w0rd"
                        invited_by: {$lender->id}
                        recaptcha: "1234567890"
                        }
                    ) {
                        token
                        status
                    }
                }
            REGISTERMUTATION;

        $this->graphQL($registerMutation)->assertStatus(200);

        $user = User::where('email', $email)->first();

        $this->assertEquals($lender->id, $user->referrer_id);

    }

    /**
     * @test
     */
    public function we_can_log_the_emails_sent_out_for_registration()
    {
        $this->assertEquals(0, EmailLog::count());

        $this->graphQL(
            handle(new RegisterMutation(['email' => 'spaghetti@64robots.com']))
        )
            ->assertSuccessful();

        $this->assertEquals(1, EmailLog::count());

        $emailLog = EmailLog::first();

        $validateEmail = new TestResponse(response()->json($emailLog));
        $validateEmail->assertJson([
            'status' => 'sent',
            'recipients' => 'spaghetti@64robots.com',
            'subject' => 'Almost Done! Please Verify Your Account',
            'failed_at' => null,
            'failed_reason' => null,
        ])
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->whereType('uuid', 'string')
                ->whereType('sent_at', 'string')
                ->etc()
        );
    }
}
