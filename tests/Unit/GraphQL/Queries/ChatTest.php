<?php

namespace Tests\Unit\GraphQL\Queries;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Message;
use App\Room;
use App\Termsheet;
use App\UserDeals;
use App\Services\RealTime\DummyRealTimeService;
use App\Services\RealTime\RealTimeServiceInterface;
use Database\Seeders\Termsheets;
use Tests\Mocks\Mutations\SendMessageBrokerMutation;
use Tests\TestCase;

class ChatTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function can_get_lender_chat(): void
    {
        $broker = Broker::factory()->create([
            'timezone' => 'America/New_York',
            'chat_response_time' => 16
        ]);

        $lender = Lender::factory()->create([
            'timezone' => 'America/New_York',
            'chat_response_time' => 10
        ]);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create(['user_id' => $broker->id]);

        UserDeals::create([
            'user_id' => $broker->id,
            'deal_id' => $deal->id,
            'relation_type' => 2
        ]);

        $findRoom = $deal->user_id . $deal->id . $lender->id;

        $room = Room::factory()->create([
            'lender_id' => $lender->id,
            'broker_id' => $broker->id,
            'deal_id' => $deal->id,
            'room' => $findRoom
        ]);


        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $broker->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(5)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $broker->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(5)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $lender->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(10)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $lender->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(10)
            ]);

        $this->actingAs($lender, 'api')
            ->graphQL('
                query {
                    chatLender( deal_id: '. $deal->id . ') {
                        name
                        room_id
                        chat_response_time
                        messages {
                            message
                            seen
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'chatLender' => [
                        'name',
                        'room_id',
                        'chat_response_time'
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function can_get_broker_chat(): void
    {
        $broker = Broker::factory()->create([
            'timezone' => 'America/New_York',
            'chat_response_time' => 10
        ]);

        $lender = Lender::factory()->create([
            'timezone' => 'America/New_York',
            'chat_response_time' => 5
        ]);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create(['user_id' => $broker->id]);

        UserDeals::create([
            'user_id' => $broker->id,
            'deal_id' => $deal->id,
            'relation_type' => 2
        ]);

        $findRoom = $deal->user_id . $deal->id . $lender->id;

        $room = Room::factory()->create([
            'lender_id' => $lender->id,
            'broker_id' => $broker->id,
            'deal_id' => $deal->id,
            'room' => $findRoom
        ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $broker->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(5)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $broker->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(5)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $lender->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(10)
            ]);

        Message::factory()->seen()
            ->create([
                'room_id' => $room->id,
                'user_id' => $lender->id,
                'created_at' => now(),
                'updated_at' => now()->addMinutes(10)
            ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    chatBroker( deal_id: '. $deal->id . ') {
                        name
                        rooms {
                           chat_response_time
                            messages {
                                seen
                                message
                            }
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'chatBroker' => [
                        'name',
                        'rooms' => [
                            '*' => [
                                'chat_response_time',
                                'messages',
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     */
    public function it_can_get_a_room_by_the_room_id()
    {
        $broker = Broker::factory()->withCompany()->create();
        $this->setupTermSheets();

        $deal = Deal::factory()->for($broker)->published()->create();
        $lender = Lender::factory()->linkedDeal($deal)->create();

        $room = Room::factory()->for($broker)->for($lender)->for($deal)->create([
            'room' => $deal->user_id . $deal->id . $lender->id,
        ]);

        $messages = Message::factory()->seen()->for($room)->for($broker, 'user')->count(2)->create();

        $this->mock(RealTimeServiceInterface::class, function ($mock) use ($room) {
            $mock->shouldReceive('makeOne')
                ->andReturn(new DummyRealTimeService())
                ->once();
        });

        // Note: In SendMessageBrokerMutation, we are passing `0` as the Lender ID.
        // This is to mock an issue that is happening on the frontend.
        // To get around this we've updated the backend to get the Room by the `Room->room` number.
        $this->actingAs($broker, 'api')
            ->graphQL(new SendMessageBrokerMutation(
                deal: $deal,
                lender: $lender,
                room: $room,
                message: 'Hello world'
            ))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'chatSendMessageBroker' => [
                        'room_id',
                        'chat' => [
                            'id',
                            'role',
                            'time',
                            'message',
                            'seen',
                            'forbidden_msg',
                        ],
                    ],
                ],
            ]);
    }
}
