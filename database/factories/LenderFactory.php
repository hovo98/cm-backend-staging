<?php

namespace Database\Factories;

use App\Company;
use App\Deal;
use App\Lender;
use App\User;
use App\UserDeals;
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

class LenderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'role' => 'lender',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'referrer_id' => User::factory(),
            'timezone' => 'America/New_York',
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'The Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                ],
                            ],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => 'Florida',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => 'Texas',
                                'formatted_address' => 'Texas, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [5],
                    'multifamily' => null,
                ],
            ],
        ];
    }

    public function linkedDeal(Deal $deal, int $lenderDealRelation = 2)
    {
        return $this->afterCreating(function ($lender) use ($deal, $lenderDealRelation) {
            UserDeals::factory()->create([
                'user_id' => $lender->id,
                'deal_id' => $deal->id,
                'relation_type' => $lenderDealRelation,
            ]);
        });
    }

    public function withCompany()
    {
        return $this->afterCreating(function (Lender $lender) {
            $company = Company::factory()->create([
                'domain' => Str::after($lender->email, '@'),
                'company_status' => 1,
            ]);

            $lender->update(['company_id' => $company->id]);
        });
    }
}
