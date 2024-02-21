<?php

namespace Tests\Feature\Commands;

use App\Broker;
use App\Console\Commands\UpdateUserResponseTime;
use App\Deal;
use App\Lender;
use App\Message;
use App\Room;
use App\Termsheet;
use App\UserDeals;
use Database\Seeders\Termsheets;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpdateUserResponseTimeCommandTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function it_should_update_response_time_for_users(): void
    {
        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();
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

        $room = Room::factory()->create([
            'lender_id' => $lender->id,
            'broker_id' => $broker->id,
            'deal_id' => $deal->id,
            'room' => 2323
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

        Artisan::call(UpdateUserResponseTime::class);


        $this->assertEquals(10, $lender->refresh()->chat_response_time);
        $this->assertEquals(5, $broker->refresh()->chat_response_time);
    }
}
