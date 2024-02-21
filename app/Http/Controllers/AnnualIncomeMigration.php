<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class AnnualIncomeMigration
 *
 * @author Nikola Popov
 */
class AnnualIncomeMigration extends Controller
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

            if (isset($mappedDeal['rent_roll']['renovationsBudget'])) {
                unset($mappedDeal['rent_roll']['renovationsBudget']);
            }

            $mappedDeal['rent_roll']['tiBudget'] = '';
            $mappedDeal['rent_roll']['lcBudget'] = '';
            $mappedDeal['rent_roll']['capExBudget'] = '';

            if ($mappedDeal['rent_roll']['increaseProjection'] === 0) {
                $mappedDeal['rent_roll']['increaseProjection'] = '';
            } elseif ($mappedDeal['rent_roll']['increaseProjection'] === 1) {
                $mappedDeal['rent_roll']['increaseProjection'] = 'Increased Occupancy';
            } elseif ($mappedDeal['rent_roll']['increaseProjection'] === 2) {
                $mappedDeal['rent_roll']['increaseProjection'] = 'Better leases';
            } elseif ($mappedDeal['rent_roll']['increaseProjection'] === 3) {
                $mappedDeal['rent_roll']['increaseProjection'] = 'CapEx/TI/LC';
            } else {
                $mappedDeal['rent_roll']['increaseProjection'] = '';
            }

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
