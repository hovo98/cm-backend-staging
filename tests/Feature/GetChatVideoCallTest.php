<?php

namespace Tests\Feature;

use App\Deal;
use App\Enums\DealPurchaseType;
use App\User;
use GraphQL\Error\Error;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GetChatVideoCallTest extends TestCase
{
    /** @test */
    public function a_user_with_subscription_can_create_zoom_meeting_urls()
    {
        $this->setupTermSheets();
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $deal = Deal::factory()
            ->for($broker, 'broker')
            ->purchased(DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION)
            ->create();

        Http::fake([
            config('services.zoom.auth_endpoint') => Http::response([
                'access_token' => '12345678',
            ]),
            config('services.zoom.api_endpoint') . '/users/me/meetings' => Http::response([
                'start_url' => 'https://example.com/s/12345678?zak=asd.efg.hij',
                'join_url' => 'https://example.com/j/12345678',
            ]),
        ]);

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    chatVideoCall(
                        deal_id: ' . $deal->id  . '
                    ){
                        start_url,
                        join_url
                    }
                }
            ')
            ->assertOk();

        $this->assertEquals('https://example.com/s/12345678?zak=asd.efg.hij', $response->json('data.chatVideoCall.start_url'));
        $this->assertEquals('https://example.com/j/12345678', $response->json('data.chatVideoCall.join_url'));

        Http::assertSentInOrder([
            fn () => true,
            function (Request $request) use ($deal) {
                $this->assertEquals('Finance Lobby ' . $deal->data['location']['street_address'], $request['agenda']);
                return true;
            },
        ]);
    }

    /** @test */
    public function a_user_without_subscription_cannot_create_zoom_meeting_urls()
    {
        $this->setupTermSheets();
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $deal = Deal::factory()->for($broker, 'broker')->create();

        Http::fake([
            config('services.zoom.auth_endpoint') => Http::response([
                'access_token' => '12345678',
            ]),
            config('services.zoom.api_endpoint') . '/users/me/meetings' => Http::response([
                'start_url' => 'https://example.com/s/12345678?zak=asd.efg.hij',
                'join_url' => 'https://example.com/j/12345678',
            ]),
        ]);

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    chatVideoCall(
                        deal_id: ' . $deal->id  . '
                    ){
                        start_url,
                        join_url
                    }
                }
            ')
            ->assertOk()
            ->assertGraphQLError(new Error('premium_deal_required'));
    }

    /**
     * @return void
     * @test
     */
    public function a_user_with_pay_per_deal_cannot_create_zoom_meeting_urls()
    {
        $this->setupTermSheets();
        $broker = User::factory()->create([
            'role' => 'broker',
        ]);

        $deal = Deal::factory()
            ->for($broker, 'broker')
            ->purchased(DealPurchaseType::PURCHASED_AS_PAY_PER_DEAL)
            ->create();

        Http::fake([
            config('services.zoom.auth_endpoint') => Http::response([
                'access_token' => '12345678',
            ]),
            config('services.zoom.api_endpoint') . '/users/me/meetings' => Http::response([
                'start_url' => 'https://example.com/s/12345678?zak=asd.efg.hij',
                'join_url' => 'https://example.com/j/12345678',
            ]),
        ]);

        $response = $this->actingAs($broker, 'api')
            ->graphQL(/** @lang GraphQL */ '
                query {
                    chatVideoCall(
                        deal_id: ' . $deal->id  . '
                    ){
                        start_url,
                        join_url
                    }
                }
            ')
            ->assertOk()
            ->assertGraphQLError(new Error('premium_deal_required'));
    }
}
