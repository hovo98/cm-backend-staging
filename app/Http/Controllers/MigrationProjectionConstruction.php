<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class MigrationProjectionConstruction
 */
class MigrationProjectionConstruction extends Controller
{
    /**
     * @var Collection
     */
    private $deals;

    /**
     * Change structure for other expenses field
     */
    public function updateConstructionProjection()
    {
        $this
            ->loadAllDeals()
            ->updateData();
    }

    /**
     * Get all deals
     *
     * @return $this
     */
    private function loadAllDeals()
    {
        $this->deals = Deal::all();

        return $this;
    }

    /**
     * @return $this
     */
    private function updateData()
    {
        $this->deals->each(function (Deal $deal) {
            $mapper = $this->getMapper($deal);
            $mappedDeal = $this->getMappedDeal($mapper);
            $user = $this->getUser($deal);

            if ($mappedDeal['property_type'] !== 0) { //
                if (Deal::PROPERTY_TYPE[$mappedDeal['property_type']] !== 'CONSTRUCTION') {
                    // $arr = [];

                    // $industrial = $this->createObjectForConstructionProjection($mappedDeal, 'industrial_rental_amount', 'industrial_sales_amount', 'INDUSTRIAL');
                    // $office = $this->createObjectForConstructionProjection($mappedDeal, 'office_rental_amount', 'office_sales_amount', 'OFFICE');
                    // $multifamily = $this->createObjectForConstructionProjection($mappedDeal, 'multifamily_rental_amount', 'multifamily_sales_amount', 'MULTIFAMILY');
                    // $retail = $this->createObjectForConstructionProjection($mappedDeal, 'retail_rental_amount', 'retail_sales_amount', 'RETAIL');

                    // if($industrial)
                    //     $arr[] = $industrial;

                    // if($office)
                    //     $arr[] = $office;

                    // if($multifamily)
                    //     $arr[] = $multifamily;

                    // if($retail)
                    //     $arr[] = $retail;

                    $mappedDeal['construction']['projectionMixedUse'] = [];

                    $mappedDeal['construction']['plans'] = '';

                    unset($mappedDeal['construction']['industrial_rental_amount']);
                    unset($mappedDeal['construction']['industrial_sales_amount']);
                    unset($mappedDeal['construction']['office_rental_amount']);
                    unset($mappedDeal['construction']['office_sales_amount']);
                    unset($mappedDeal['construction']['multifamily_rental_amount']);
                    unset($mappedDeal['construction']['multifamily_sales_amount']);
                    unset($mappedDeal['construction']['retail_rental_amount']);
                    unset($mappedDeal['construction']['retail_sales_amount']);
                }
            } else {
                $mappedDeal['construction']['projectionMixedUse'] = [];
                $mappedDeal['construction']['plans'] = '';

                unset($mappedDeal['construction']['industrial_rental_amount']);
                unset($mappedDeal['construction']['industrial_sales_amount']);
                unset($mappedDeal['construction']['office_rental_amount']);
                unset($mappedDeal['construction']['office_sales_amount']);
                unset($mappedDeal['construction']['multifamily_rental_amount']);
                unset($mappedDeal['construction']['multifamily_sales_amount']);
                unset($mappedDeal['construction']['retail_rental_amount']);
                unset($mappedDeal['construction']['retail_sales_amount']);
            }
            //     } else {
            //         $mappedDeal['construction']['projectionMixedUse'] = [];
            //     }
            // } else {
            //     $mappedDeal['construction']['projectionMixedUse'] = [];
            // }

            if (isset($mappedDeal['lastStepStatus'])) {
                unset($mappedDeal['lastStepStatus']);
            }

            // Persist data
            $this->persistData($mapper, $mappedDeal, $user);
        });

        return $this;
    }

    private function createObjectForConstructionProjection($mappedDeal, $rental, $sale, $tag)
    {
        $obj = [
            'tag' => $tag,
            'projections' => '',
            'projections_sales' => '',
            'projections_per_units' => '',
            'projections_per_sf' => '',
            'rental_per' => '',
            'rental_amount' => '',
            'rental_projections_per_units' => '',
            'rental_projections_per_sf' => '',
            'plans' => '',
            'second_projection' => false,
            'plansOrder' => [],
        ];

        if (isset($mappedDeal['construction'][$rental])) {
            if ($mappedDeal['construction'][$rental] !== '' && $mappedDeal['construction'][$rental] !== null) {
                $obj['projections'] = '';
                $obj['rental_per'] = 'unit';
                $obj['projections_sales'] = '';
                $obj['rental_amount'] = $mappedDeal['construction'][$rental];
                $obj['second_projection'] = false;
                $obj['plans'] = 'rent';
                $obj['plansOrder'] = ['rent', 'sell'];
            } else {
                return false;
            }
        } else {
            if (isset($mappedDeal['construction'][$sale])) {
                if ($mappedDeal['construction'][$sale] !== '' && $mappedDeal['construction'][$sale] !== null) {
                    $obj['projections'] = 'unit';
                    $obj['rental_per'] = '';
                    $obj['projections_sales'] = $mappedDeal['construction'][$sale];
                    $obj['rental_amount'] = '';
                    $obj['second_projection'] = false;
                    $obj['plans'] = 'sell';
                    $obj['plansOrder'] = ['sell', 'rent'];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return $obj;
    }

    private function getMapper(Deal $deal)
    {
        try {
            return new DealMapper($deal->id);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            throw $e;
        }
    }

    private function getMappedDeal($mapper)
    {
        return $mapper->mapFromEloquent();
    }

    private function getUser(Deal $deal)
    {
        return User::find($deal->user_id);
    }

    /**
     * Save changed structure of field
     *
     * @return $this
     */
    private function persistData(DealMapper $mapper, array $mappedDeal, \Illuminate\Foundation\Auth\User $user)
    {
        $deal = $mapper->mapToEloquent($mappedDeal, $user);
        $deal->save();

        return $this;
    }
}
