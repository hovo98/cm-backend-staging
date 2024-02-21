<?php

namespace Database\Factories;

use App\AssetTypes;
use App\Broker;
use App\Deal;
use App\Enums\DealPurchaseType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Mocks\DealData;

class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $brokers;

        if ($brokers === null) {
            $brokers = Broker::all();
        }

        return [
            'user_id' => 14,
            'finished' => true,
            'updated_at' => '2021-01-28 13:11:50',
            'finished_at' => '2021-05-12 11:22:33',
            'termsheet' => 1,
            'purchase_type' => DealPurchaseType::NOT_PURCHASED,
            'data' => [
                'purchase_loan' => [
                    'price' => 1111,
                    'estimated_value' => 1,
                    'estimated_cap_rate' => 1,
                    'days_to_close' => 1,
                    'loan_amount' => 111111,
                    'ltc_purchase' => '12,3',
                ],
                'refinance_loan' => [
                    'purchasePrice' => 1111,
                    'date' => '2021-05-12',
                    'currentValue' => 1111,
                    'list' => 'aaaa',
                    'loanAmount' => 1111,
                ],
                'owner_occupied' => [
                    'business_name' => '',
                    'business_description' => '',
                    'sales_amount' => '',
                    'profit_amount' => '',
                    'borrower_own' => '',
                    'business_age' => '',
                    'sales_amount_YTD' => '',
                    'profit_amount_YTD' => '',
                    'employees' => '',
                ],
                'inducted' => [
                    'loan_type' => 3,
                    'loan_amount' => 70000,
                    'property_type' => [
                        'type' => 3,
                        'mixed' => false,
                        'asset_types' => [1, 5],
                    ],
                ],
                'block_and_lot' => [
                    'block' => 3,
                    'lot' => 7,
                ],
                'expenses' => [
                    'taxNumber' => '22',
                    'tax' => '22',
                    'expDate' => '2021-01-28',
                    'phaseStructure' => '',
                    'payroll' => '',
                    'insurance' => '',
                    'repairs' => '',
                    'payrollAmount' => '',
                    'electricity' => 'true',
                    'electricityAmount' => '22',
                    'gas' => '',
                    'gasAmount' => '',
                    'commonArea' => '',
                    'commonAreaAmount' => '',
                    'water' => '',
                    'waterAmount' => '',
                    'management' => '',
                    'managementAmount' => '',
                    'legal' => '',
                    'triple' => '',
                    'reimbursement' => '',
                    'otherExpenses' => '',
                    'additionalNotes' => '',
                    'elevatorMaintenanceAmount' => '',
                    'elevatorMaintenance' => '',
                    'ooSewerAmount' => '',
                    'gasSeparatelyMetered' => '',
                    'managementCompanyName' => '',
                    'ooWaterAmount' => '',
                    'waterSeparatelyMetered' => '',
                    'electricitySeparatelyMetered' => '',
                ],
                'rent_roll' => [
                    'table' => [
                        [
                            'unit_type' => 'aa',
                            'name' => 'aa',
                            'unit' => 'a',
                            'bedroom' => 'a',
                            'lease_start' => '2021-01-28',
                            'lease_end' => '2021-01-28',
                            'sf' => 's',
                            'monthle_rent' => '3',
                            'annual_rent' => '33',
                        ],
                    ],
                    'annual_income' => '2',
                    'potential_income' => '2',
                    'increaseProjection' => '2',
                    'increasedNotes' => 'ss',
                    'betterNotes' => 's',
                    'timeFrame' => 's',
                    'plannedImprovements' => 's',
                ],
                'loan_type' => 3,
                'property_type' => 0,
                'existing' => [
                    'free' => '',
                    'lender' => '',
                    'warehouse' => '',
                    'propertyType' => '',
                ],
                'location' => [
                    'city' => 'New York',
                    'state' => 'New York',
                    'place_id' => 'EiVSb2NrYXdheSBCbHZkLCBKYW1haWNhLCBOWSAxMTQyMCwgVVNBIi4qLAoUChIJvwUN7ARnwokRis6q-7YPLsMSFAoSCTOf5OM-Z8KJEbQh_cW9UlzH',
                    'zip_code' => '11225',
                    'sublocality' => 'The Bronx',
                    'street_address' => 'Rockaway Boulevard',
                    'street_address_2' => '',
                ],
                'construction_loan' => [
                    'buying_land' => 'true',
                    'debt_on_property' => 'true',
                    'purchase_price' => 1111111,
                    'purchase_date' => '2021-5-11',
                    'debt_amount' => 'true',
                    'lender_name' => 'lalal',
                    'loanAmount' => 10000,
                ],
                'construction' => [
                    'date' => '2021-5-11',
                    'land_cost' => 2,
                    'current_value' => 2,
                    'hard_cost' => 2,
                    'soft_cost' => 2,
                    'loan_amount' => 2,
                    'contractor_name' => 'saasas',
                    'amount_units' => 222,
                    'square_footage' => 11,
                    'floors' => 11,
                    'projections' => 'true',
                    'projections_sales' => 22,
                    'projections_per_units' => 2,
                    'projections_per_sf' => 2,
                    'rental_per' => 'true',
                    'rental_amount' => 22,
                    'retail_sales_amount' => 2,
                    'retail_rental_amount' => 2,
                    'multifamily_sales_amount' => 2,
                    'multifamily_rental_amount' => 2,
                    'office_sales_amount' => 2,
                    'office_rental_amount' => 2,
                    'industrial_sales_amount' => 2,
                    'industrial_rental_amount' => 2,
                ],
                'sponsor' => [
                    'liabilities' => '2',
                    'sponsorInfo' => [
                        [
                            'name' => 'Anna',
                            'ownership' => '3',
                        ],
                        [
                            'name' => 'Jack',
                            'ownership' => '3',
                        ],
                        [
                            'name' => 'Smith',
                            'ownership' => '94',
                        ],
                    ],
                    'assets_other' => '3',
                    'annual_income' => '3',
                    'assets_liquid' => '3',
                    'annual_expenses' => '3',
                    'assets_companies' => '3',
                    'years_experience' => '',
                    'family_experience' => '',
                    'assets_real_estate' => '3',
                ],
                'investment_details' => [
                    'mixedUse' => [],
                    'propType' => 8,
                    'numberUnit' => 44,
                    'retailType' => '',
                    'multiAmount' => null,
                    'multiSquare' => null,
                    'multiNumberOfUnitsOccupied' => null,
                    'multiSquareFootageOccupied' => null,
                    'proposedUse' => '',
                    'noteToLender' => '',
                    'officeAmount' => null,
                    'officeSquare' => null,
                    'officeNumberOfUnitsOccupied' => null,
                    'officeSquareFootageOccupied' => null,
                    'retailAmount' => null,
                    'retailSquare' => null,
                    'retailNumberOfUnitsOccupied' => null,
                    'retailSquareFootageOccupied' => null,
                    'squareFootage' => 44,
                    'warehouseAmount' => null,
                    'warehouseSquare' => null,
                    'warehouseNumberOfUnitsOccupied' => null,
                    'warehouseSquareFootageOccupied' => null,
                    'numberUnitOccupied' => 44,
                    'squareFootageOccupied' => null,
                ],
            ],
        ];
    }

    public function dealLimitReached()
    {
        return $this->state(['quote_limit_reached' => true]);
    }

    public function finished()
    {
        return $this->state([
            'finished' => true,
            'finished_at' => now()
        ]);
    }

    public function withData($data)
    {
        return $this->state([
            'data' => $data
        ]);
    }

    public function notPublished()
    {
        return $this->state(function (array $attributes) {
            return [
                'finished' => false,
                'finished_at' => null,
            ];
        });
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'finished' => true,
                'finished_at' => now(),
            ];
        });
    }

    public function purchased(DealPurchaseType $purchaseType = DealPurchaseType::PURCHASED_AS_PAY_PER_DEAL)
    {
        return $this->state(function (array $attributes) use ($purchaseType) {
            return [
                'premiumed_at' => now(),
                'purchase_type' => $purchaseType,
            ];
        });
    }

    public function pricedAt(int $price)
    {
        return $this->state(function (array $attributes) use ($price) {
            return [
                'dollar_amount' => $price,
                'data' => handle(new DealData([
                    'purchase_loan' => [
                        'price' => $price,
                        'loan_amount' => $price,
                        'ltc_purchase' => '80.00 %',
                        'days_to_close' => null,
                        'estimated_value' => 0,
                        'estimated_cap_rate' => null,
                    ],
                    "location" => [
                        "street_address" => "East Sunrise Highway",
                        "city"=> "New York",
                        "sublocality"=> "",
                        "state"=> "New York",
                        "country"=> "United States",
                        "zip_code"=> "11520",
                        "place_id"=> "ChIJOwg_06VPwokRYv534QaPC8g",
                        "county"=> "Nassau County",
                        "street"=> "East Sunrise Highway",
                    ],
                    "inducted" => [
                        "property_type" => [
                            "mixed" => false,
                            "asset_types" => [1,5]
                        ],
                        'loan_type' => 2
                    ]
                ]))
            ];
        })
        ->afterCreating(function (Deal $deal) {
            // create the corresponding asset type
            AssetTypes::factory()
                ->create([
                    'id' => 4,
                    'title' => 'Construction',
                ]);
        });
    }
}
