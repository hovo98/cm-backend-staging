<?php

namespace App\Services\VideoCall;

use Illuminate\Support\Facades\Http;

class ZoomVideoCall implements VideoCallInterface
{
    private $joinUrl;
    private $startUrl;

    public function createRoomUrl(string $agenda): void
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->createAccessToken(),
        ])->post(config('services.zoom.api_endpoint') . '/users/me/meetings', [
            'agenda' => $agenda,
        ]);

        if ($response->json('code')) {
            throw new ZoomAccessTokenException($response->json('message'));
        }

        if ($response->json('start_url') && $response->json('join_url')) {
            $this->joinUrl = $response->json('join_url');
            $this->startUrl = $response->json('start_url');
        }
    }

    public function getJoinUrl(): string
    {
        if (!$this->joinUrl) {
            throw new ZoomUrlCreationException('We are unable to create a join url. Try again later.');
        }

        return $this->joinUrl;
    }

    public function getStartUrl(): string
    {
        if (!$this->startUrl) {
            throw new ZoomUrlCreationException('We are unable to create a start url. Try again later.');
        }

        return $this->startUrl;
    }

    /**
     * @see https://developers.zoom.us/docs/internal-apps/s2s-oauth/
     */
    private function createAccessToken(): string
    {
        $zoomDomain = config('services.zoom.domain');
        $zoomClientId = config('services.zoom.client_id');
        $zoomClientSecret = config('services.zoom.client_secret');
        $zoomBasicToken = base64_encode($zoomClientId . ':' . $zoomClientSecret);
        $zoomAccountId = config('services.zoom.account_id');
        $response = Http::asForm()
            ->withHeaders([
                'Host' => $zoomDomain,
                'Authorization' => 'Basic ' . $zoomBasicToken,
            ])
            ->post(config('services.zoom.auth_endpoint'), [
                'grant_type' => 'account_credentials',
                'account_id' => $zoomAccountId,
            ]);

        if ($response->json('reason')) {
            throw new ZoomAccessTokenException($response->json('reason'));
        }

        return $response->json('access_token');
    }


}
