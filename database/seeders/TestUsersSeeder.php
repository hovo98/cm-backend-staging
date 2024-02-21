<?php

namespace Database\Seeders;

use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 1; $i++) {
            $broker = new User([
                'role' => 'broker',
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'password' => '$2y$10$jdLWLkyQGoy11htFRxOHFeYqsptDgepp9l2OapVRtk2K3jr56J566', // password
                'remember_token' => Str::random(10),
                'email_verified_at' => '2022-03-17 12:36:10',
                'company_id' => 14,
                'beta_user' => true,
            ]);
            $broker->save();
        }

        for ($i = 0; $i < 3; $i++) {
            $metas = [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'lat' => 27.6648274,
                                'city' => '',
                                'long' => -81.5157535,
                                'state' => 'Florida',
                                'county' => '',
                                'country' => 'United States',
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'zip_code' => '',
                                'long_name' => 'Florida',
                                'sublocality' => '',
                                'polygon_location' => 'polygons/3914/working/lat_37.09024_long_-95.712891.json',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'lat' => 37.09024,
                                'city' => '',
                                'long' => -95.712891,
                                'state' => '',
                                'county' => '',
                                'country' => 'United States',
                                'place_id' => 'ChIJCzYy5IS16lQRQrfeQ5K5Oxw',
                                'zip_code' => '',
                                'long_name' => 'United States',
                                'sublocality' => '',
                                'polygon_location' => 'polygons/29/working/lat_37.09024_long_-95.712891.json',
                                'formatted_address' => 'United States',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'lat' => 40.4172871,
                                'city' => '',
                                'long' => -82.90712300000001,
                                'state' => 'Ohio',
                                'county' => '',
                                'country' => 'United States',
                                'place_id' => 'ChIJwY5NtXrpNogRFtmfnDlkzeU',
                                'zip_code' => '',
                                'long_name' => 'Ohio',
                                'sublocality' => '',
                                'polygon_location' => 'polygons/3914/working/lat_37.09024_long_-95.712891.json',
                                'formatted_address' => 'Ohio, USA',
                            ],
                            'exclusions' => [],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 200000000,
                        'min' => 1,
                    ],
                    'asset_types' => [
                        1,
                        2,
                        3,
                        4,
                        5,
                        8,
                        7,
                        6,
                    ],
                    'multifamily' => null,
                    'type_of_loans' => [
                        1,
                        2,
                        3,
                        4,
                    ],
                    'other_asset_types' => [
                        1,
                        2,
                        3,
                        4,
                        5,
                        6,
                        7,
                    ],
                ],
            ];
            $lender = new User([
                'role' => 'lender',
                'first_name' => $faker->firstName(),
                'last_name' => $faker->lastName(),
                'email' => $faker->unique()->safeEmail(),
                'password' => '$2y$10$jdLWLkyQGoy11htFRxOHFeYqsptDgepp9l2OapVRtk2K3jr56J566', // password
                'remember_token' => Str::random(10),
                'email_verified_at' => '2022-03-17 12:36:10',
                'company_id' => 12,
                'beta_user' => true,
                // 'referrer_id' => $faker->numberBetween($min = 12354, $max = 13253),
                'metas' => json_decode(json_encode($metas)),
            ]);
            $lender->save();
        }
    }
}
