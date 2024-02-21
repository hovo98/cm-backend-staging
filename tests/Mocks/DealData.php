<?php

namespace Tests\Mocks;

class DealData
{
    public function __construct(protected array $attributes = [])
    {
    }

    public function __invoke()
    {
        return array_merge([
            "id" => 6,
            "step" => "dealSponsor",
            "assets" => [
            ],
            "sponsor" => [
                "liabilities" => "2",
                "sponsorInfo" => [
                    [
                        "name" => "John Doe",
                        "ownership" => "50"
                    ],
                    [
                        "name" => "s2",
                        "ownership" => "50"
                    ]
                ],
                "assets_other" => "2",
                "annual_income" => "2",
                "assets_liquid" => "2",
                "annual_expenses" => "2",
                "assets_companies" => "2",
                "years_experience" => "1",
                "family_experience" => "false",
                "assets_real_estate" => "2"
            ],
            "user_id" => 2,
            "existing" => [
                "free" => "",
                "lender" => "",
                "warehouse" => "",
                "propertyType" => ""
            ],
            "expenses" => [
                "gas" => "",
                "tax" => "false",
                "legal" => "2",
                "water" => "",
                "triple" => "false",
                "expDate" => "",
                "payroll" => "",
                "repairs" => "2",
                "gasAmount" => "",
                "insurance" => "2",
                "taxNumber" => "2",
                "commonArea" => "",
                "management" => "",
                "electricity" => "",
                "waterAmount" => "",
                "ooSewerAmount" => "",
                "ooWaterAmount" => "",
                "otherExpenses" => "2",
                "payrollAmount" => "",
                "reimbursement" => "",
                "phaseStructure" => "",
                "additionalNotes" => "2",
                "commonAreaAmount" => "",
                "managementAmount" => "",
                "electricityAmount" => "",
                "elevatorMaintenance" => "",
                "gasSeparatelyMetered" => "",
                "managementCompanyName" => "",
                "waterSeparatelyMetered" => "",
                "elevatorMaintenanceAmount" => "",
                "electricitySeparatelyMetered" => ""
            ],
            "finished" => false,
            "location" => [
                "city" => "Greensboro",
                "state" => "North Carolina",
                "place_id" => "EiFHZ28gRHIsIEdyZWVuc2Jvcm8sIE5DIDI3NDA2LCBVU0EiLiosChQKEgmr3HKIPj1TiBFSShbov9aeGBIUChIJeXvHOD8ZU4gRyBK-eJTEuZM",
                "zip_code" => "27406",
                "sublocality" => "Greensboro",
                "street_address" => "Ggo Drive",
                "street_address_2" => ""
            ],
            "directive" => null,
            "loan_type" => 1,
            "rent_roll" => [
                "table" => [
                    [
                        "sf" => "",
                        "name" => "",
                        "unit" => "",
                        "bedroom" => "",
                        "lease_end" => "",
                        "unit_type" => "",
                        "annual_rent" => "",
                        "lease_start" => "",
                        "monthle_rent" => ""
                    ]
                ],
                "timeFrame" => "",
                "betterNotes" => "",
                "annual_income" => "",
                "increasedNotes" => "",
                "potential_income" => "",
                "increaseProjection" => "",
                "plannedImprovements" => ""
            ],
            "updated_at" => null,
            "upload_pfs" => [
                "multiple" => "",
                "liabilities" => "",
                "sponsorInfo" => [
                    "name" => "",
                    "ownership" => ""
                ],
                "assets_other" => "",
                "annual_income" => "",
                "assets_liquid" => "",
                "annual_expenses" => "",
                "assets_companies" => "",
                "years_experience" => "",
                "family_experience" => "",
                "assets_real_estate" => ""
            ],
            "sensitivity" => [
                "fees" => 0,
                "leverage" => 0,
                "recourse" => 0,
                "timeToClose" => 0,
                "dollarAmount" => 0,
                "interestRate" => 0
            ],
            "construction" => [
                "date" => "",
                "floors" => 0,
                "payroll" => "",
                "hard_cost" => 0,
                "land_cost" => 0,
                "soft_cost" => 0,
                "rental_per" => 0,
                "loan_amount" => 0,
                "projections" => "",
                "amount_units" => 0,
                "current_value" => 0,
                "payroll_phase" => "",
                "rental_amount" => 0,
                "square_footage" => 0,
                "contractor_name" => "",
                "projections_sales" => 0,
                "projections_per_sf" => 0,
                "projections_per_units" => 0
            ],
            "block_and_lot" => [
                "lot" => "",
                "block" => "",
                "blockAndLot" => ""
            ],
            "property_type" => 1,
            "purchase_loan" => [
                "price" => 2,
                "loan_amount" => 2,
                "ltc_purchase" => "100.00 %",
                "days_to_close" => 0,
                "estimated_value" => 0,
                "estimated_cap_rate" => null
            ],
            "lastStepStatus" => "",
            "owner_occupied" => [
                "employees" => "",
                "borrower_own" => "",
                "business_age" => "",
                "sales_amount" => "",
                "business_name" => "",
                "profit_amount" => "",
                "sales_amount_YTD" => "",
                "profit_amount_YTD" => "",
                "business_description" => ""
            ],
            "refinance_loan" => [
                "date" => "",
                "list" => "",
                "loanAmount" => 0,
                "currentValue" => 0,
                "purchasePrice" => 0
            ],
            "construction_loan" => [
                "loanAmount" => 0,
                "buying_land" => "",
                "debt_amount" => 0,
                "lender_name" => "",
                "purchase_date" => "",
                "purchase_price" => 0,
                "debt_on_property" => ""
            ],
            "investment_details" => [
                "mixedUse" => [
                    1,
                    8
                ],
                "propType" => 4,
                "numberUnit" => null,
                "retailType" => "2",
                "multiAmount" => 2,
                "multiFloors" => 2,
                "multiSquare" => 2,
                "proposedUse" => "",
                "noteToLender" => "",
                "officeAmount" => null,
                "officeFloors" => null,
                "officeSquare" => null,
                "retailAmount" => 2,
                "retailFloors" => 2,
                "retailSquare" => 2,
                "squareFootage" => null,
                "warehouseAmount" => null,
                "warehouseFloors" => null,
                "warehouseSquare" => null,
                "numberUnitOccupied" => null,
                "squareFootageOccupied" => null
            ]
        ], $this->attributes);
    }
}
