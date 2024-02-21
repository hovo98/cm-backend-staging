<?php

namespace Tests\Services\MapperServices\Lender\Deals;

use App\Broker;
use App\Company;
use App\Deal;
use App\Quote;
use App\Services\MapperServices\Lender\Deals\FilterDealsLender;
use App\User;
use App\UserDeals;
use Illuminate\Support\Carbon;
use Tests\Mocks\DealData;
use Tests\TestCase;

class FilterDealsLenderTest extends TestCase
{
    /** @test */
    public function it_is_not_showing_deleted_lender_quotes()
    {
        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);
        $broker = Broker::factory()->create([
            'company_id' => $company->id,
        ]);
        $this->setupTermSheets();
        $data = handle(new DealData([
            'purchase_loan' => [
                'price' => 10000000,
                'loan_amount' => 10000000,
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
        ]));
        $deal = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData(json_encode($data))
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);
        $dealUser = UserDeals::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $broker->id,
        ]);
        $lender = User::factory()->create(['role' => 'lender']);
        $lenderToBeDeleted = User::factory()->create(['role' => 'lender']);
        $visibleQuotes = Quote::factory(2)
            ->finished()
            ->for($deal)
            ->for($lender, 'lender')
            ->create();
        $hiddenQuotes = Quote::factory(1)
            ->finished()
            ->for($deal)
            ->for($lenderToBeDeleted, 'lender')
            ->create();
        $lenderToBeDeleted->deleted_at = Carbon::now();
        $lenderToBeDeleted->save();

        $data = (new FilterDealsLender())->map([
            [
                'data' => [$deal],
            ],
            null,
            null,
            null,
            User::find($broker->id),
        ]);

        $this->assertCount(2, $data['data'][0]['quotes']);
    }
}
