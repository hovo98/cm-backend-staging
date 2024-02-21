<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role' => 'broker',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'chat_response_time' => 5,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function broker()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'broker',
            ];
        });
    }

    public function emailVerified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => now(),
            ];
        });
    }
}
