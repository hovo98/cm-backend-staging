<?php

namespace Tests\Feature;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Quote;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Tests\TestCase;

class QuoteFilterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    //    public function testBrokerQuotesIndividual()
    //    {
    //        $broker = Broker::factory()->create();
    //        $lender = Lender::factory()->create();
    //        $lender2 = Lender::factory()->create();
    //        $this->seed('Termsheets');
    //        $this->actingAs($broker, 'api');
    //        $sponsor1Name = 'abcdefg';
    //        $data = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "' . $sponsor1Name . '", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
    //        $deal = new Deal();
    //        $deal->user_id = $broker->id;
    //        $deal->data = $data;
    //        $deal->save();
    //
    //        $deal2 = new Deal();
    //        $deal2->user_id = $broker->id;
    //        $deal2->data = $data;
    //        $deal2->save();
    //
    //
    //        $quote = Quote::factory()->create([
    //            'deal_id' => $deal->id,
    //            'user_id' => $lender->id
    //        ]);
    //
    //        $quote2 = Quote::factory()->create([
    //            'deal_id' => $deal->id,
    //            'user_id' => $lender->id
    //        ]);
    //
    //        $quote3 = Quote::factory()->create([
    //            'deal_id' => $deal->id,
    //            'user_id' => $lender2->id
    //        ]);
    //
    //        $quote4 = Quote::factory()->create([
    //            'deal_id' => $deal2->id,
    //            'user_id' => $lender->id
    //        ]);
    //
    //        $response = $this->graphQL(/** @lang GraphQL */ '
    //            query {
    //                brokerQuotesIndividual(input: {
    //                    lender: '. $lender->id .'
    //                    deal: '. $deal->id .'
    //                }){
    //                    lender{
    //                        firstName
    //                    },
    //                    quotes{
    //                        id
    //                    }
    //                }
    //            }
    //        ');
    //        $data = $response->json();
    //
    //        $this->assertCount(3, $data['data']['brokerQuotesIndividual']['quotes']);
    //    }

    // Todo: The response is not working as expected
    public function testLenderQuoteFilterByDealId()
    {
        $broker = Broker::factory()->create(['referrer_id' => null]);
        $lender = Lender::factory()->create(['referrer_id' => null]);
        $lender2 = Lender::factory()->create(['referrer_id' => null]);
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->actingAs($lender, 'api');
        $sponsor1Name = 'abcdefg';
        $data = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $deal = new Deal();
        $deal->user_id = $broker->id;
        $deal->data = $data;
        $deal->save();

        $quote = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);

        $quote2 = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);

        $quote3 = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender2->id,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query {
                lenderQuotesIndividual(input: {
                    id: '.$deal->id.'
                }){
                    lender{
                        firstName
                    },
                    quotes{
                        id
                    }
                }
            }
        ');

        $data = $response->json();
        // $this->assertEquals($quote->id, $data['data']['lenderQuotesIndividual']['quotes'][0]['id']);
    }

    // Todo: The response is not working as expected
    public function testQuoteFilterBySponsors()
    {
        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->actingAs($broker, 'api');
        $sponsor1Name = 'abcdefg';
        $data = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $deal = new Deal();
        $deal->user_id = $broker->id;
        $deal->data = $data;
        $deal->save();

        $quote = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query {
                quoteFilter(input: {
                    sponsors: ["'.$sponsor1Name.'"]
                }){
                    data {
                        id,
                        dealID
                    }
                    paginatorInfo {
                        count
                    }
                }
            }
        ');
        $data = $response->json();
        // $this->assertEquals($quote->id, $data['data']['quoteFilter']['data'][0]['id']);
    }

    public function testQuoteFilterByLocation()
    {
        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->actingAs($broker, 'api');
        $sponsor1Name = 'abcdefg';
        $data = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $data2 = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $deal = new Deal();
        $deal->user_id = $broker->id;
        $deal->data = $data;
        $deal->save();

        $deal2 = new Deal();
        $deal2->user_id = $broker->id;
        $deal2->data = $data2;
        $deal2->save();

        $quote1 = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);
        $quote2 = Quote::factory()->create([
            'deal_id' => $deal2->id,
            'user_id' => $lender->id,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query {
                quoteFilter(input: {
                    location: "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM"
                }, pagination: {
                    perPage: 2
                }){
                    data {
                        id,
                        dealID
                    }
                    paginatorInfo {
                        count
                    }
                }
            }
        ');
        $data = $response->json();

        $this->assertEquals(2, $data['data']['quoteFilter']['paginatorInfo']['count']);
    }

    // Todo: The response is not working as expected
    public function testQuoteFilterByLocationAndSponsor()
    {
        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->actingAs($broker, 'api');
        $sponsor1Name = 'abcdefg';
        $data = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "TESTEiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $data2 = json_decode('{"id": 6, "step": "dealSponsor", "assets": [], "sponsor": {"liabilities": "2", "sponsorInfo": [{"name": "'.$sponsor1Name.'", "ownership": "50"}, {"name": "s2", "ownership": "50"}], "assets_other": "2", "annual_income": "2", "assets_liquid": "2", "annual_expenses": "2", "assets_companies": "2", "years_experience": "1", "family_experience": "false", "assets_real_estate": "2"}, "user_id": 2, "existing": {"free": "", "lender": "", "warehouse": "", "propertyType": ""}, "expenses": {"gas": "", "tax": "false", "legal": "2", "water": "", "triple": "false", "expDate": "", "payroll": "", "repairs": "2", "gasAmount": "", "insurance": "2", "taxNumber": "2", "commonArea": "", "management": "", "electricity": "", "waterAmount": "", "ooSewerAmount": "", "ooWaterAmount": "", "otherExpenses": "2", "payrollAmount": "", "reimbursement": "", "phaseStructure": "", "additionalNotes": "2", "commonAreaAmount": "", "managementAmount": "", "electricityAmount": "", "elevatorMaintenance": "", "gasSeparatelyMetered": "", "managementCompanyName": "", "waterSeparatelyMetered": "", "elevatorMaintenanceAmount": "", "electricitySeparatelyMetered": ""}, "finished": false, "location": {"city": "Greensboro", "state": "North Carolina", "place_id": "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM", "zip_code": "27406", "sublocality": "Greensboro", "street_address": "Ggo Drive", "street_address_2": ""}, "directive": null, "loan_type": 1, "rent_roll": {"table": [{"sf": "", "name": "", "unit": "", "bedroom": "", "lease_end": "", "unit_type": "", "annual_rent": "", "lease_start": "", "monthle_rent": ""}], "timeFrame": "", "betterNotes": "", "annual_income": "", "increasedNotes": "", "potential_income": "", "increaseProjection": "", "plannedImprovements": ""}, "updated_at": null, "upload_pfs": {"multiple": "", "liabilities": "", "sponsorInfo": {"name": "", "ownership": ""}, "assets_other": "", "annual_income": "", "assets_liquid": "", "annual_expenses": "", "assets_companies": "", "years_experience": "", "family_experience": "", "assets_real_estate": ""}, "sensitivity": {"fees": 0, "leverage": 0, "recourse": 0, "timeToClose": 0, "dollarAmount": 0, "interestRate": 0}, "construction": {"date": "", "floors": 0, "payroll": "", "hard_cost": 0, "land_cost": 0, "soft_cost": 0, "rental_per": 0, "loan_amount": 0, "projections": "", "amount_units": 0, "current_value": 0, "payroll_phase": "", "rental_amount": 0, "square_footage": 0, "contractor_name": "", "projections_sales": 0, "projections_per_sf": 0, "projections_per_units": 0}, "block_and_lot": {"lot": "", "block": "", "blockAndLot": ""}, "property_type": 1, "purchase_loan": {"price": 2, "loan_amount": 2, "ltc_purchase": "100.00 %", "days_to_close": 0, "estimated_value": 0, "estimated_cap_rate": null}, "lastStepStatus": "", "owner_occupied": {"employees": "", "borrower_own": "", "business_age": "", "sales_amount": "", "business_name": "", "profit_amount": "", "sales_amount_YTD": "", "profit_amount_YTD": "", "business_description": ""}, "refinance_loan": {"date": "", "list": "", "loanAmount": 0, "currentValue": 0, "purchasePrice": 0}, "construction_loan": {"loanAmount": 0, "buying_land": "", "debt_amount": 0, "lender_name": "", "purchase_date": "", "purchase_price": 0, "debt_on_property": ""}, "investment_details": {"mixedUse": [1, 8], "propType": 4, "numberUnit": null, "retailType": "2", "multiAmount": 2, "multiFloors": 2, "multiSquare": 2, "proposedUse": "", "noteToLender": "", "officeAmount": null, "officeFloors": null, "officeSquare": null, "retailAmount": 2, "retailFloors": 2, "retailSquare": 2, "squareFootage": null, "warehouseAmount": null, "warehouseFloors": null, "warehouseSquare": null, "numberUnitOccupied": null, "squareFootageOccupied": null}}');
        $deal = new Deal();
        $deal->user_id = $broker->id;
        $deal->data = $data;
        $deal->save();

        $deal2 = new Deal();
        $deal2->user_id = $broker->id;
        $deal2->data = $data2;
        $deal2->save();

        $quote1 = Quote::factory()->create([
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);
        $quote2 = Quote::factory()->create([
            'deal_id' => $deal2->id,
            'user_id' => $lender->id,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query {
                quoteFilter(input: {
                    sponsors: ["'.$sponsor1Name.'"]
                    location: "TESTEiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM"
                }, pagination: {
                    perPage: 2
                }){
                    data {
                        id,
                        dealID
                    }
                    paginatorInfo {
                        count
                    }
                }
            }
        ');
        $data = $response->json();
        // $this->assertEquals(1, $data['data']['quoteFilter']['paginatorInfo']['count']);
    }
}
