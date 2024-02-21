<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\DataTransferObjects\Fit;
use App\DataTransferObjects\FitArea;
use App\Events\UserChanged;
use App\Lender;
use App\User;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateFit
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class UpdateFit
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @param  bool  $is_created
     * @return array
     *
     * @throws Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo, $is_created = false)
    {
        if ($context->user()->role !== 'lender') {
            throw new Error('Only Lenders have the Perfect Fit and Close Fit.');
        }

        $lender = Lender::find($context->user()->id);

        if (! empty($args['multifamily']) && $args['multifamily']['min_amount'] && $args['multifamily']['max_amount']) {
            if (($args['multifamily']['min_amount'] > $args['multifamily']['max_amount']) || ($args['multifamily']['min_amount'] === $args['multifamily']['max_amount'])) {
                return [
                    'success' => false,
                    'message' => 'Min and max values cannot be equal. 
                    Min value cannot be larger than max value.',
                ];
            }
            if (($args['multifamily']['min_amount'] < 2) || ($args['multifamily']['max_amount'] > 99000)) {
                return [
                    'success' => false,
                    'message' => 'Enter a value between 4-99,000',
                ];
            }
        }

        $areas = $this->getPolygonFromArgs($args['areas'], $lender->id);

        $fit = new Fit($areas, $args['loan_size'], $args['asset_types'], $args['multifamily'] ?? [], $args['other_asset_types'] ?? [], $args['type_of_loans'] ?? []);

        $updated = $lender->updateFit(strtolower($args['type']), $fit);

        if (! $updated) {
            throw new Error('An error occured, please try again');
        }

        if ($is_created) {
            event(new UserChanged($lender, 'createdLender'));
        }

        return [
            'success' => true,
            'message' => 'Your profile has been updated',
        ];
    }

    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function create($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $payload = decrypt($args['token']);

        $user = User::whereId($payload['id'])->whereEmail($payload['email'])->first();

        if (! $user instanceof Authenticatable) {
            throw new AuthenticationException('An error occured, please try again');
        }

        $args = collect($args)->except('token')->toArray();
        $context->user = $user;

        return $this->resolve($rootValue, $args, $context, $resolveInfo, true);
    }

    // For each area store file and save the path to Fit
    private function getPolygonFromArgs($areas, int $lenderId)
    {
        foreach ($areas as $key => $area) {
            $single_area = $area['area'];
            $link_polygon = $this->storePolygon($single_area['polygon_location'], $lenderId, FitArea::AREA_TYPE_WORKING_DIRECTORY, $single_area['lat'], $single_area['long']);
            $area['area']['polygon_location'] = $link_polygon;
            // Check if exclusions are empty
            if (! empty($area['exclusions'])) {
                $exclusions = $this->storePolygonExclusions($area['exclusions'], $lenderId);
                $area['exclusions'] = $exclusions;
            }
            $areas[$key] = $area;
        }

        return $areas;
    }

    // For each exclusion store file and save the path to Fit
    private function storePolygonExclusions($exclusions, int $lenderId)
    {
        foreach ($exclusions as $key => $exclusion) {
            $link_polygon_exclusions = $this->storePolygon($exclusion['polygon_location'], $lenderId, FitArea::AREA_TYPE_EXCLUDED_DIRECTORY, $exclusion['lat'], $exclusion['long']);
            $exclusion['polygon_location'] = $link_polygon_exclusions;
            $exclusions[$key] = $exclusion;
        }

        return $exclusions;
    }

    /**
     * Store json polygon on filesystem
     *
     * @param  string  $polygon
     * @param  int  $lenderId
     * @param  string  $areaType
     * @param  float  $latitude
     * @param  float  $longitude
     * @return string
     */
    private function storePolygon(string $polygon, int $lenderId, string $areaType, float $latitude, float $longitude): string
    {
        if (! $polygon) {
            return '';
        }

        $filePathAndName = sprintf(
            '%s/%s/%s/lat_%s_long_%s.json',
            FitArea::POLYGON_STORAGE_DIRECTORY,
            $lenderId,
            $areaType,
            $latitude,
            $longitude
        );

        $response = Storage::disk(config('app.app_file_upload'))->put($filePathAndName, $polygon, []);

        return $filePathAndName;
    }
}
