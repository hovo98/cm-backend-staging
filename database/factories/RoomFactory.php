<?php

namespace Database\Factories;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'broker_id' => Broker::factory(),
            'lender_id' => Lender::factory(),
            'deal_id' => Deal::factory(),
            'company' => $this->faker->company,
        ];
    }
}
