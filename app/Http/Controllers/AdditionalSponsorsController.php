<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Deal;

class AdditionalSponsorsController extends Controller
{
    private $deals;

    public function updateFields()
    {
        $deals = Deal::withTrashed()->get();
        $deals
        ->each(function (Deal $deal) {
            $this->deal = $deal;
            $data = $deal->data;

            echo '<pre>';
            // print_r($data);
            print_r($data['sponsor']);
            echo '</pre>';

            $name = $data['sponsor']['sponsorInfo'][0]['name'];
            $ownership = $data['sponsor']['sponsorInfo'][0]['ownership'];
            $liabilities = $data['sponsor']['liabilities'] ?? '';
            $assets_other = $data['sponsor']['assets_other'] ?? '';
            $annual_income = $data['sponsor']['annual_income'] ?? '';
            $assets_liquid = $data['sponsor']['assets_liquid'] ?? '';
            $annual_expenses = $data['sponsor']['annual_expenses'] ?? '';
            $assets_companies = $data['sponsor']['assets_companies'] ?? '';
            $years_experience = $data['sponsor']['years_experience'] ?? '';
            $family_experience = $data['sponsor']['family_experience'] ?? '';
            $assets_real_estate = $data['sponsor']['assets_real_estate'] ?? '';
            $net_worth = $data['sponsor']['net_worth'] ?? '';
            $net_income = $data['sponsor']['net_income'] ?? '';
            $total_assets = $data['sponsor']['total_assets'] ?? '';

            $sponsorInfo = [];
            $sponsorInfo['name'] = $name;
            $sponsorInfo['ownership'] = $ownership;
            $sponsorInfo['liabilities'] = $liabilities;
            $sponsorInfo['assets_other'] = $assets_other;
            $sponsorInfo['annual_income'] = $annual_income;
            $sponsorInfo['assets_liquid'] = $assets_liquid;
            $sponsorInfo['annual_expenses'] = $annual_expenses;
            $sponsorInfo['assets_companies'] = $assets_companies;
            $sponsorInfo['years_experience'] = $years_experience;
            $sponsorInfo['family_experience'] = $family_experience;
            $sponsorInfo['assets_real_estate'] = $assets_real_estate;
            $sponsorInfo['net_worth'] = $net_worth;
            $sponsorInfo['net_income'] = $net_income;
            $sponsorInfo['total_assets'] = $total_assets;

            unset($data['sponsor']['sponsorInfo']);
            unset($data['sponsor']['liabilities']);
            unset($data['sponsor']['assets_other']);
            unset($data['sponsor']['annual_income']);
            unset($data['sponsor']['assets_liquid']);
            unset($data['sponsor']['annual_expenses']);
            unset($data['sponsor']['assets_companies']);
            unset($data['sponsor']['years_experience']);
            unset($data['sponsor']['family_experience']);
            unset($data['sponsor']['assets_real_estate']);
            unset($data['sponsor']['net_worth']);
            unset($data['sponsor']['net_income']);
            unset($data['sponsor']['total_assets']);

            // $data['sponsor']['sponsorInfo'] = $sponsorInfo;
            $data['sponsor'] = ['sponsorInfo' => [$sponsorInfo]];

            $deal->data = $data;
            $deal->save();

            echo '<br><br><br>';

            echo '<pre>';
            print_r($data['sponsor']);
            echo '</pre>';

            echo '<br><br>';
            echo json_encode($data['sponsor']);
        });
        echo 'Done';
    }
}
