<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Lender;
use Illuminate\Http\JsonResponse;

class Heatmap extends Controller
{
    private const HEATMAP_API_KEY = 'HEATMAP_API_KEY';

    public function __construct()
    {
        $this->API_KEY = config('app.HEATMAP_API_KEY');
    }

    /**
     * @param  API_KEY  $key
     * @return JsonResponse
     */
    public function index($key): JsonResponse
    {
        if ($this->API_KEY === $key) {
            return response()->json($this->getData(), 200);
        }

        return response()->json('Bad API Key', 401);
    }

    /**
     * @return object
     */
    private function getData(): object
    {
        $lenders = Lender::where('role', '=', 'lender')->orderBy('created_at', 'ASC')->cursor();
        $mapedLender = $this->mapLender($lenders);

        return $mapedLender;
    }

    /**
     * @param  object  $lenders
     * @return object
     */
    private function mapLender(object $lenders): object
    {
        $lenders = $lenders->map(function ($lender, $key) {
            $company = $lender->getCompany();
            $fit = $lender->getPerfectFit();
            if (is_null($fit)) {
                return [
                    'First name' => $lender->first_name,
                    'Last name' => $lender->last_name,
                    'Email' => $lender->email,
                ];
            }
            $areas = $fit->getAreas();

            // $inclusions = [];
            // $exclusions = [];

            // foreach ($areas as $area) {
            //     $exclusions = array_merge($exclusions, $area->formattedExclusions());
            //     $inclusions[] = (object) ['address' => $area->formattedArea()];
            // }

            $loanSize = $fit->getLoanSize();
            $assetType = $fit->getAssetTypesNames();

            return [
                'First name' => $lender->first_name,
                'Last name' => $lender->last_name,
                'Email' => $lender->email,
                'Company name' => $company['company_name'] ?? '',
                // 'Exclusions' => $exclusions,
                // 'Inclusions' => $inclusions,
                'Loan max' => $loanSize->min(),
                'Loan min' => $loanSize->max(),
                'Asset type' => $fit->getAssetTypesNames() ?? [],
                'Other asset type' => $fit->getOtherAssetTypesNames() ?? [],
                'Areas' => $areas,
            ];
        });

        return $lenders;
    }
}
