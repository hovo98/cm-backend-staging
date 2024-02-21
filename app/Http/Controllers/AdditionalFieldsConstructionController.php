<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Deal;

class AdditionalFieldsConstructionController extends Controller
{
    private $deals;

    public function updateFields()
    {
        $deals = Deal::withTrashed()->get();
        $deals
        ->each(function (Deal $deal) {
            $this->deal = $deal;
            $data = $deal->data;

            $amountOfUnits = $data['investment_details']['amountOfUnits'] ?? '';
            $rentableSellable = $data['investment_details']['rentableSellable'] ?? '';
            $retailFloors = $data['investment_details']['retailFloors'] ?? '';
            $multiAmountOfUnits = $data['investment_details']['multiAmountOfUnits'] ?? '';
            $multiRentableSellable = $data['investment_details']['multiRentableSellable'] ?? '';
            $multiFloors = $data['investment_details']['multiFloors'] ?? '';
            $officeAmountOfunits = $data['investment_details']['officeAmountOfunits'] ?? '';
            $officeRentableSellable = $data['investment_details']['officeRentableSellable'] ?? '';
            $officeFloors = $data['investment_details']['officeFloors'] ?? '';
            $industrialAmountOfUnits = $data['investment_details']['industrialAmountOfUnits'] ?? '';
            $industrialRentableSellable = $data['investment_details']['industrialRentableSellable'] ?? '';
            $industrialFloors = $data['investment_details']['industrialFloors'] ?? '';

            $investment_details = $data['investment_details'];
            $investment_details['amountOfUnits'] = $amountOfUnits;
            $investment_details['rentableSellable'] = $rentableSellable;
            $investment_details['retailFloors'] = $retailFloors;
            $investment_details['multiAmountOfUnits'] = $multiAmountOfUnits;
            $investment_details['multiRentableSellable'] = $multiRentableSellable;
            $investment_details['multiFloors'] = $multiFloors;
            $investment_details['officeAmountOfunits'] = $officeAmountOfunits;
            $investment_details['officeRentableSellable'] = $officeRentableSellable;
            $investment_details['officeFloors'] = $officeFloors;
            $investment_details['industrialAmountOfUnits'] = $industrialAmountOfUnits;
            $investment_details['industrialRentableSellable'] = $industrialRentableSellable;
            $investment_details['industrialFloors'] = $industrialFloors;

            $data['investment_details'] = $investment_details;

            $deal->data = $data;
            $deal->save();
        });

        echo 'Done';
    }
}
