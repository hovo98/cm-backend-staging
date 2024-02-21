<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Class FitArea
 *
 * Contains the data for the Lender's Deal Preferences Areas, with Exclusions included
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class FitArea
{
    public const POLYGON_STORAGE_DIRECTORY = 'polygons';

    public const AREA_TYPE_WORKING_DIRECTORY = 'working';

    public const AREA_TYPE_EXCLUDED_DIRECTORY = 'excluded';

    /**
     * @var array
     */
    public $area;

    /**
     * @var array
     */
    public $exclusions = [];

    /**
     * FitLocation constructor.
     *
     * @param  array  $area
     */
    public function __construct(array $area)
    {
        $this->area = $area['area'];

        if (isset($area['exclusions'])) {
            $this->exclusions = $area['exclusions'];
        }
    }

    /**
     * @return array
     */
    public function exclusions(): array
    {
        return $this->exclusions;
    }

    /**
     * @return array
     */
    public function formattedExclusions(): array
    {
        $exclusions = [];

        foreach ($this->exclusions() as $exclusion) {
            $exclusions[] = $exclusion['formatted_address'];
        }

        return $exclusions;
    }

    /**
     * @return array
     */
    public function area(): array
    {
        return $this->area;
    }

    /**
     * @return string
     */
    public function formattedArea(): string
    {
        return $this->area['formatted_address'] ?: '';
    }

    /**
     * @return array
     */
    public function workingArea(): array
    {
        return $this->area;
    }

    /**
     * @return array
     */
    public function excludedArea(): array
    {
        $exclusions = [];

        foreach ($this->exclusions() as $exclusion) {
            $exclusions[] = $exclusion;
        }

        return $exclusions;
    }

    /**
     * @param  callable  $mapCallback
     */
    public function mapPolygon(callable $mapCallback)
    {
        $this->area['polygon_location'] = $mapCallback();

        return $this;
    }

    /**
     * @param  callable  $mapCallback Mandatory input parameters: $mapCallback(float $lat, float $long);
     * @return $this
     */
    public function mapExclusions(callable $mapCallback)
    {
        foreach ($this->exclusions as $key => $exclusion) {
            $exclusion['polygon_location'] = $mapCallback($exclusion['lat'], $exclusion['long']);
            $this->exclusions[$key] = $exclusion;
        }

        return $this;
    }

    public function fipsArea(callable $mapCallback)
    {
        $res = $mapCallback($this->area['lat'], $this->area['long'], $this->area['formatted_address']);
        if ($res === '') {
            $this->area['fips_code'] = '';
        }

        return $this;
    }

    public function fipsExclusions(callable $mapCallback)
    {
        foreach ($this->exclusions as $key => $exclusion) {
            $res = $mapCallback($this->area['lat'], $this->area['long'], $this->area['formatted_address']);
            if ($res === '') {
                $this->exclusion['fips_code'] = '';
            }

            $this->exclusions[$key] = $exclusion;
        }

        return $this;
    }
}
