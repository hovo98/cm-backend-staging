<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use App\DataTransferObjects\Fit;
use App\DataTransferObjects\FitArea;
use App\Lender;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetFit
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class GetFit
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     *
     * @throws Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($context->user()->role !== 'lender') {
            throw new Error('Only lenders can access their Deal Configuration.');
        }

        /** @var Lender $lender */
        $lender = Lender::find($context->user()->id);

        if ($args['type'] === 'PERFECT') {
            $fit = $lender->getPerfectFit();
        } else {
            $fit = $lender->getCloseFit();
        }

        $areas = new Collection(
            $fit instanceof Fit
                ? $fit->getAreas()
                : []
        );

        $this->mapPolygonsIntoAreas($areas, $lender->id);
        $showTypeOfLoans = $this->checkTypeOfLoansFit($fit);

        return [
            'areas' => $areas->toArray(),
            'loan_size' => $fit instanceof Fit ? $fit->getLoanSize() : [],
            'asset_types' => $fit instanceof Fit ? $fit->getAssetTypes() : [],
            'multifamily' => $fit instanceof Fit ? $fit->getMultifamily() : [],
            'other_asset_types' => $fit instanceof Fit ? $fit->getOtherAssetTypes() : [],
            'type_of_loans' => $fit instanceof Fit ? $fit->getTypeOfLoansLender() : [],
            'show_type_of_loans' => $showTypeOfLoans,
        ];
    }

    private function mapPolygonsIntoAreas(Collection $areas, int $lenderId)
    {
        $areas->each(function (FitArea $area) use ($lenderId) {
            $area
                ->mapPolygon(
                    function () use ($lenderId, $area) {
                        return $this->getPolygon($lenderId, FitArea::AREA_TYPE_WORKING_DIRECTORY, $area->area['lat'], $area->area['long']);
                    }
                )
                ->mapExclusions(
                    function (float $latitude, float $longitude) use ($lenderId) {
                        return $this->getPolygon($lenderId, FitArea::AREA_TYPE_EXCLUDED_DIRECTORY, $latitude, $longitude);
                    }
                );
        });

        return $areas;
    }

    /**
     * @param  int  $lenderId
     * @param  string  $areaType
     * @param  float  $latitude
     * @param  float  $longitude
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function getPolygon(int $lenderId, string $areaType, float $latitude, float $longitude)
    {
        $filePathAndName = sprintf(
            '%s/%s/%s/lat_%s_long_%s.json',
            FitArea::POLYGON_STORAGE_DIRECTORY,
            $lenderId,
            $areaType,
            $latitude,
            $longitude
        );
        if (Storage::disk(config('app.app_file_upload'))->exists($filePathAndName)) {
            return Storage::disk(config('app.app_file_upload'))->get($filePathAndName);
        }

        return '';
        //return Storage::disk('cm-server-storage')->get($filePathAndName);
    }

    /**
     * @param $fit
     * @return bool
     */
    private function checkTypeOfLoansFit($fit): bool
    {
        $typeOfLoans = true;
        if (! $fit) {
            return $typeOfLoans;
        }
        $getTypeOfLoans = $fit->getTypeOfLoansLender();
        if (! empty($getTypeOfLoans)) {
            $typeOfLoans = false;
        }

        return $typeOfLoans;
    }
}
