<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'message' => $this->faker->paragraph,
            'seen' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * @return MessageFactory
     */
    public function seen()
    {
        return $this->state([
            'seen' => true
        ]);
    }

    /**
     * @return MessageFactory
     */
    public function unseen()
    {
        return $this->state([
            'seen' => false
        ]);
    }
}
