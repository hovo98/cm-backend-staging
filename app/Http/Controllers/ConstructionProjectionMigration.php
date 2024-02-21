<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class ConstructionProjectionMigration
 *
 * @author Nikola Popov
 */
class ConstructionProjectionMigration extends Controller
{
    /**
     * @var Collection
     */
    private $deals;

    /**
     * Change structure for other expenses field
     */
    public function updateRentRoll()
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
        $this->deals = Deal::where('user_id', 8365);

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

            $mappedDeal['construction']['projectionMixedUse'] = [];
            $mappedDeal['construction']['plans'] = '';
            $mappedDeal['construction']['second_projection'] = false;

            unset($mappedDeal['construction']['industrial_rental_amount']);
            unset($mappedDeal['construction']['industrial_sales_amount']);
            unset($mappedDeal['construction']['office_rental_amount']);
            unset($mappedDeal['construction']['office_sales_amount']);
            unset($mappedDeal['construction']['multifamily_rental_amount']);
            unset($mappedDeal['construction']['multifamily_sales_amount']);
            unset($mappedDeal['construction']['retail_rental_amount']);
            unset($mappedDeal['construction']['retail_sales_amount']);

            if (isset($mappedDeal['lastStepStatus'])) {
                unset($mappedDeal['lastStepStatus']);
            }

            $this->persistData($mapper, $mappedDeal, $user);
        });

        return $this;
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
