<?php

namespace Tests\Feature;

use App\Broker;
use App\Company;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Tests\TestCase;

class StoreDealTest extends TestCase
{
    /**
     * @test
     * Todo: The response is not working as expected
     */
    public function create_deal()
    {
        $company = Company::factory()->create();
        $broker = Broker::factory()->create(['company_id' => $company->id]);
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->actingAs($broker, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
                mutation {
                    deal(input: {
                        location: {
                            street_address: "",
                            street_address_2: "",
                            city: "",
                            state: "",
                            zip_code: "",
                            place_id: "",
                            sublocality: "",
                            country: "",
                            county: "",
                            street: "",
                        }
                    }){
                        id
                    }
                }
        ');
        $data = $response->json();
        // $deal = $data['data']['deal'];
        // $this->assertTrue(boolval($deal['id']));
    }

    /**
     * @test
     * Todo: The response is not working as expected
     *
     * @return void
     * */
    public function update_deal()
    {
        $company = Company::factory()->create();
        $broker = Broker::factory()->create(['company_id' => $company->id]);

        $this->seed('Termsheets');


        $this->actingAs($broker, 'api');
        $response = $this->graphQL(/** @lang GraphQL */ '
                mutation {
                    deal(input: {
                        location: {
                            street_address: "",
                            street_address_2: "",
                            city: "",
                            state: "",
                            zip_code: "",
                            place_id: "",
                            sublocality: "",
                            country: "",
                            county: "",
                            street: "",
                        }
                    }){
                        id
                    }
                }
        ')->assertOk();
        $data = $response->json();
        // $deal = $data['data']['deal'];
        // $this->assertTrue(boolval($deal['id']));
    }
}
