<?php

namespace Tests\Controllers;

use App\Enums\OAuthExceptionCode;
use App\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Laravel\Passport\Exceptions\OAuthServerException;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;
use Tests\Mocks\Mutations\LoginInvalidPasswordMutation;
use Tests\Mocks\SentryNotifiedException;
use Tests\Mocks\SentryNotNotifiedException;
use Tests\TestCase;
use Throwable;

class SentryExceptionReportingTest extends TestCase
{
    /**
     * @test
     */
    public function the_system_can_correctly_identify_an_oauth_exception_by_code()
    {
        // Mock how Lighhouse set's up exceptions
        $inheritedThrowable = LeagueException::invalidCredentials();
        $throwable = new OAuthServerException($inheritedThrowable, new Response());

        $this->assertEquals(
            OAuthExceptionCode::INVALID_CREDENTIALS,
            OAuthExceptionCode::from($throwable->getCode())
        );
    }

    /**
     * @test
     */
    public function sentry_wont_log_an_invalid_credentials_error()
    {
        // Override the Exception Handler
        $this->allowReportingOnExceptions();
        $this->expectException(OAuthServerException::class);
        $this->expectException(SentryNotNotifiedException::class);

        // Confirm a mock acception that shows working as expected
        $oldPassportConfig = $this->getOldPassportConfigAndSetNew();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->graphQL(handle(new LoginInvalidPasswordMutation([
                'email' => $user->email
            ])))
            ->assertJson([])
            ->assertStatus(403)
            ->assertJsonPath('errors.0.extensions.reason', 'Incorrect username or password.');

        $this->resetPassportConfig($oldPassportConfig);
    }

    /**
     * Override the Exception Handler For Testing
     */
    public function allowReportingOnExceptions()
    {
        $originalHandler = app(ExceptionHandler::class);

        $this->app->instance(ExceptionHandler::class, new class ($originalHandler) extends \App\Exceptions\Handler {
            protected $originalHandler;
            protected $except = [];

            protected $dontReport = [
                SentryNotifiedException::class,
                SentryNotNotifiedException::class,
            ];

            public function __construct($originalHandler)
            {
                $this->originalHandler = $originalHandler;
            }

            /**
             * Override for testing
             ** SentryNotifiedException --> Thrown If Sentry Would be Notified
             ** SentryNotNotifiedException --> Thrown if Sentry Would not be notified
             * @throws \Exception
             */
            public function report(Throwable $e)
            {
                if ($this->shouldReportInSentry($e)) {
                    throw new SentryNotifiedException();
                } else {
                    throw new SentryNotNotifiedException();
                }
            }

            public function shouldReport(Throwable $e)
            {
                $dontReport = array_merge($this->dontReport);

                return is_null(Arr::first($dontReport, fn ($type) => $e instanceof $type));
            }

            public function renderForConsole($output, Throwable $e)
            {
                (new ConsoleApplication())->renderThrowable($e, $output);
            }
        });
    }
}
//
