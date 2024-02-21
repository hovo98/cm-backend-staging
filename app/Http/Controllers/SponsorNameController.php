<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Deal;

class SponsorNameController extends Controller
{
    private $deals;

    public function updateField()
    {
        $deals = Deal::withTrashed()->get();
        $deals
        ->each(function (Deal $deal) {
            $this->deal = $deal;
            $data = $deal->data;

            $sponsorInfo = $data['sponsor']['sponsorInfo'];

            $deal->sponsor_name = ucfirst($sponsorInfo[0]['name']) ?? '';

            $deal->save();
        });
        echo 'Done';
    }
}
