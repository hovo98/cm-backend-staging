<?php

declare(strict_types=1);

namespace App\Exports\Adapters;

use App\Config;
use Exception;
use Google\Exception as GoogleException;
use Google_Client;
use Google_Service_Sheets;

/**
 * Class GoogleSheet
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class GoogleSheet implements AdapterInterface
{
    private const GOOGLE_SHEET_AUTH_TOKEN = 'GOOGLE_SHEET_AUTH_TOKEN';

    private const GOOGLE_SHEET_CREDENTIALS = 'GOOGLE_SHEET_CREDENTIALS';

    private const GOOGLE_SHEET_ID = 'GOOGLE_SHEET_ID';

    /** @var Google_Client */
    private $client;

    /** @var Config */
    private $token;

    /** @var ?Exception */
    public $error;

    /** @var string */
    private $sheetId;

    /** @var string */
    private $authUrl;

    public function __construct()
    {
        try {
            $this->bootstrapTokenConfig()
                 ->bootstrapGoogleClient();

            $this->sheetId = config('app.google_sheet_id', '');
        } catch (Exception $e) {
            $this->error = $e;
        }
    }

    public function appendRows(string $sheetName, array $data): AdapterInterface
    {
        $service = new Google_Service_Sheets($this->client);

        $body = new \Google_Service_Sheets_ValueRange([
            'values' => $data,
        ]);

        try {
            $service->spreadsheets_values->append($this->sheetId, "${sheetName}", $body, [
                'valueInputOption' => 'RAW',
            ]);
        } catch (Exception $e) {
            $this->error = $e;
        }

        return $this;
    }

    public function insertHeadings(string $sheetName, $headings)
    {
        $service = new Google_Service_Sheets($this->client);
        $body = new \Google_Service_Sheets_ValueRange();

        $body->setValues([$headings]);

        try {
            $service->spreadsheets_values->update($this->sheetId, "${sheetName}!A1", $body, [
                'valueInputOption' => 'RAW',
            ]);
        } catch (Exception $e) {
            $this->error = $e;
        }

        return $this;
    }

    public function emptySheet(array $manifest): AdapterInterface
    {
        $service = new Google_Service_Sheets($this->client);
        $body = new \Google_Service_Sheets_BatchClearValuesRequest();

        $ranges = collect($manifest['sheets'])->pluck('title')->toArray();
        $body->setRanges($ranges);

        try {
            $service->spreadsheets_values->batchClear($this->sheetId, $body);
        } catch (Exception $e) {
            $this->error = $e;
        }

        return $this;
    }

    /**
     * Check if the Client has an error
     *
     * @return array
     */
    public function checkAuthorization(): array
    {
        if ($this->error) {
            return [
                'success' => false,
                'error' => $this->error->getMessage(),
                'authUrl' => $this->authUrl,
            ];
        }

        return [
            'success' => true,
        ];
    }

    /**
     * Try authenticating with the AuthCode
     *
     * @param  string  $authCode
     * @return array
     */
    public function authenticateCode(string $authCode)
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (array_key_exists('error', $token)) {
            return [
                'success' => false,
                'error' => 'AuthCode is wrong, please try again.',
                'authUrl' => $this->authUrl,
            ];
        }

        $this->client->setAccessToken($token);
        $this->token->setAttribute('value', json_encode($this->client->getAccessToken()))->save();

        return [
            'success' => true,
        ];
    }

    /**
     * @return string
     */
    public function sheetUrl(): string
    {
        if (! $this->sheetId) {
            return '';
        }

        return sprintf('https://docs.google.com/spreadsheets/d/%s/edit', $this->sheetId);
    }

    /**
     * Refresh the token
     *
     * @return GoogleSheet
     *
     * @throws GoogleException
     */
    private function refreshToken(): GoogleSheet
    {
        if (! $this->client->getRefreshToken()) {
            throw new GoogleException('An error has occured. Please try again.');
        }

        $token = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

        if (array_key_exists('error', $token)) {
            throw new GoogleException($token['error']);
        }

        $this->token->setAttribute('value', json_encode($this->client->getAccessToken()))->save();

        return $this;
    }

    /**
     * Load the token Config
     *
     * If it does not exist, create a new empty one
     *
     * @return GoogleSheet
     */
    private function bootstrapTokenConfig(): GoogleSheet
    {
        if ($this->token === null) {
            $this->token = Config::where('key', '=', self::GOOGLE_SHEET_AUTH_TOKEN)->first();

            if ($this->token === null) {
                $this->token = (new Config([
                    'key' => self::GOOGLE_SHEET_AUTH_TOKEN,
                    'value' => '',
                ]))->save();
            }
        }

        return $this;
    }

    /**
     * Initialize the Google Client and verify Authorization
     *
     * @throws GoogleException
     */
    private function bootstrapGoogleClient()
    {
        $this->client = new Google_Client();

        // Set the defaults
        $this->client->setApplicationName('Google Sheets API PHP Quickstart');
        $this->client->setScopes(Google_Service_Sheets::SPREADSHEETS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setRedirectUri(route('export-data-authorize'));

        // Set the credentials
        $config = json_decode(base64_decode(config('app.google_sheet_credentials', '')), true);

        if (! isset($config['web'])) {
            throw new GoogleException('Invalid json for auth config.
            Please check <code>'.config('app.google_sheet_credentials').'</code> environment variable,
            it should be a JSON object, base64 encoded.');
        }

        $this->client->setAuthConfig($config);

        $this->authUrl = $this->client->createAuthUrl();

        // Set access token
        if (! $this->token || ! $this->token instanceof Config || ! $this->token->getAttribute('value')) {
            throw new GoogleException('You are not authorized to view this file. Please contact the development team for assistance.');
        }

        $this->client->setAccessToken($this->token->getAttribute('value'));

        // Check if it is expired and try refreshing
        if ($this->client->isAccessTokenExpired()) {
            $this->refreshToken();
        }

        // Check again
        if ($this->client->isAccessTokenExpired()) {
            throw new GoogleException('You are not authorized to view this file. Please contact the development team for assistance.');
        }
    }
}
