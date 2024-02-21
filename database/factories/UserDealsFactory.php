<?php

namespace Database\Factories;

use App\Deal;
use App\Lender;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\UserDeals>
 */
class UserDealsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => Lender::factory(),
            'deal_id' => Deal::factory(),
            'relation_type' => User::LENDER_ARCHIVE_DEAL,
        ];
    }
}
