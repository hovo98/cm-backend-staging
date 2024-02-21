<?php

namespace Tests\Services\VideoCall;

use App\Services\VideoCall\ZoomVideoCall;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZoomVideoCallTest extends TestCase
{
    /** @test */
    public function it_can_create_a_meeting_url()
    {
        Http::fake([
            config('services.zoom.auth_endpoint') => Http::response([
                'access_token' => '12345678',
            ]),
            config('services.zoom.api_endpoint') . '/users/me/meetings' => Http::response([
                'start_url' => 'https://example.com/s/12345678?zak=asd.efg.hij',
                'join_url' => 'https://example.com/j/12345678',
            ]),
        ]);

        $zoomVideoCall = new ZoomVideoCall();

        $zoomVideoCall->createRoomUrl('Finance Lobby Test');

        $this->assertEquals('https://example.com/s/12345678?zak=asd.efg.hij', $zoomVideoCall->getStartUrl());
        $this->assertEquals('https://example.com/j/12345678', $zoomVideoCall->getJoinUrl());

        Http::assertSentInOrder([
            function (Request $request) {
                $this->assertEquals('zoom.us', $request->header('Host')[0]);
                $this->assertEquals('account_credentials', $request['grant_type']);
                return true;
            },
            function (Request $request) {
                $this->assertEquals('api.zoom.us', $request->header('Host')[0]);
                $this->assertEquals('Finance Lobby Test', $request['agenda']);
                return true;
            },
        ]);
    }
}
