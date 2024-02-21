<?php

namespace Tests\Feature;

use App\Mail\InvitationEmailBrokerToBroker;
use App\Mail\InvitationEmailBrokerToLender;
use App\Mail\InvitationEmailLenderToBroker;
use App\Mail\InvitationEmailLenderToLender;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteEmailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_lender_can_invite_a_lender()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["new_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailLenderToLender::class, function ($mailable) use ($lender) {
            $this->assertEquals($lender->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_lender_can_invite_an_existent_lender()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        $existentInvitedLender = User::factory()->create([
            'role' => 'lender',
            'email'=> 'existent_invited_lender@example.com',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["existent_invited_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailLenderToLender::class, function ($mailable) use ($lender) {
            $this->assertEquals($lender->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_lender_cannot_invite_a_trashed_lender()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        $trashedInvitedLender = User::factory()->create([
            'role' => 'lender',
            'email'=> 'trashed_invited_lender@example.com',
            'deleted_at' => now(),
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["trashed_invited_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToLender::class);
    }

    /** @test */
    public function a_lender_cannot_invite_an_incorrect_lender_email()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["new_lender_example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEquals([ ["email" => "new_lender_example.com", "message" => "Please enter a valid email"] ], $response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToLender::class);
    }

    /** @test */
    public function a_lender_can_invite_a_broker()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["new_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailLenderToBroker::class, function ($mailable) use ($lender) {
            $this->assertEquals($lender->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_lender_can_invite_an_existent_broker()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        $existentInvitedBroker = User::factory()->create([
            'role' => 'broker',
            'email'=> 'existent_invited_broker@example.com',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["existent_invited_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailLenderToBroker::class, function ($mailable) use ($lender) {
            $this->assertEquals($lender->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_lender_cannot_invite_a_trashed_broker()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        $trashedInvitedBroker = User::factory()->create([
            'role' => 'broker',
            'email'=> 'trashed_invited_broker@example.com',
            'deleted_at' => now(),
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["trashed_invited_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToBroker::class);
    }

    /** @test */
    public function a_lender_cannot_invite_an_incorrect_broker_email()
    {
        $lender = User::factory()->create([
            'role' => 'lender',
        ]);

        Mail::fake();

        $response = $this->actingAs($lender, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["new_broker_example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEquals([ ["email" => "new_broker_example.com", "message" => "Please enter a valid email"] ], $response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToBroker::class);
    }

    /** @test */
    public function a_broker_can_invite_a_lender()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["new_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailBrokerToLender::class, function ($mailable) use ($broker) {
            $this->assertEquals($broker->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_broker_can_invite_an_existent_lender()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $existentInvitedLender = User::factory()->create([
            'role' => 'lender',
            'email'=> 'existent_invited_lender@example.com',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["existent_invited_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailBrokerToLender::class, function ($mailable) use ($broker) {
            $this->assertEquals($broker->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_broker_cannot_invite_a_trashed_lender()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $trashedInvitedLender = User::factory()->create([
            'role' => 'lender',
            'email'=> 'trashed_invited_lender@example.com',
            'deleted_at' => now(),
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["trashed_invited_lender@example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailBrokerToLender::class);
    }

    /** @test */
    public function a_broker_cannot_invite_an_incorrect_lender_email()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: ["new_lender_example.com"], inviteBroker: []}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEquals([ ["email" => "new_lender_example.com", "message" => "Please enter a valid email"] ], $response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToBroker::class);
    }

    /** @test */
    public function a_broker_can_invite_a_broker()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["new_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailBrokerToBroker::class, function ($mailable) use ($broker) {
            $this->assertEquals($broker->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_broker_can_invite_an_existent_broker()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $existentInvitedBroker = User::factory()->create([
            'role' => 'broker',
            'email'=> 'existent_invited_broker@example.com',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["existent_invited_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertSent(InvitationEmailBrokerToBroker::class, function ($mailable) use ($broker) {
            $this->assertEquals($broker->first_name, $mailable->senderName);
            return true;
        });
    }

    /** @test */
    public function a_broker_cannot_invite_a_trashed_broker()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $trashedInvitedBroker = User::factory()->create([
            'role' => 'broker',
            'email'=> 'trashed_invited_broker@example.com',
            'deleted_at' => now(),
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["trashed_invited_broker@example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailBrokerToBroker::class);
    }

    /** @test */
    public function a_broker_cannot_invite_an_incorrect_broker_email()
    {
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        Mail::fake();

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    invitationEmail(
                        input:  {inviteLender: [], inviteBroker: ["new_broker_example.com"]}
                    ){
                        errorsResponseBroker { email, message },
                        errorsResponseLender { email, message }
                    }
                }
            ')
            ->assertOk();

        $this->assertEquals([ ["email" => "new_broker_example.com", "message" => "Please enter a valid email"] ], $response->json('data.invitationEmail.errorsResponseBroker'));
        $this->assertEmpty($response->json('data.invitationEmail.errorsResponseLender'));
        Mail::assertNotSent(InvitationEmailLenderToBroker::class);
    }
}
