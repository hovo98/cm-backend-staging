<?php

namespace Tests;

use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MakesGraphQLRequests;
    use RefreshDatabase;

    public function getOldPassportConfigAndSetNew(): array
    {
        $this->artisan('passport:install');
        $passportClientId = Config::get('lighthouse-graphql-passport.client_id');
        $passportClientSecret = Config::get('lighthouse-graphql-passport.client_secret');
        $clientData = DB::table('oauth_clients')->get()->last();
        Config::set('lighthouse-graphql-passport.client_id', $clientData->id);
        Config::set('lighthouse-graphql-passport.client_secret', $clientData->secret);

        return [
            'client_id' => $passportClientId,
            'client_secret' => $passportClientSecret,
        ];
    }

    public function resetPassportConfig(array $passportConfig)
    {
        Config::set('lighthouse-graphql-passport.client_id', $passportConfig['client_id']);
        Config::set('lighthouse-graphql-passport.client_secret', $passportConfig['client_secret']);
    }

    public function setupTermSheets()
    {
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
    }
}
