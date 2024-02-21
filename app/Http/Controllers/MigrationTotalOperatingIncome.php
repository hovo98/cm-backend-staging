<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class MigrationTotalOperatingIncome
 */
class MigrationTotalOperatingIncome extends Controller
{
    /**
     * @var Collection
     */
    private $deals;

    /**
     * Change structure for other expenses field
     */
    public function updateCalculation()
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

            if ($mappedDeal['property_type'] !== 0) {
                if (Deal::PROPERTY_TYPE[$mappedDeal['property_type']] === 'OWNER_OCCUPIED') {
                    if ($mappedDeal['expenses']['totalExpenses'] !== '') {
                        Log::info($mappedDeal['id']);
                        $mappedDeal['expenses']['totalBusinessOperatingIncome'] = $this->calculateTotalExpensesOwnerOccupied($mappedDeal);
                    }
                }
            }

            if (isset($mappedDeal['lastStepStatus'])) {
                unset($mappedDeal['lastStepStatus']);
            }

            // Persist data
            $this->persistData($mapper, $mappedDeal, $user);
        });

        return $this;
    }

    private function calculateTotalExpensesOwnerOccupied($mappedDeal): string
    {
        $total = (float) str_replace(',', '', $mappedDeal['expenses']['totalExpenses']);
        $totalBusinessOperatingIncome = 0;
        $profit_amount = (float) str_replace(',', '', $mappedDeal['owner_occupied']['profit_amount']);
        $totalBusinessOperatingIncome = $profit_amount - $total;

        $total = $total === 0 ? '' : (string) $total;

        return $totalBusinessOperatingIncome ? (string) $totalBusinessOperatingIncome : '';
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

    private function getUser(Deal $deal)
    {
        return User::find($deal->user_id);
    }

    private function getMappedDeal($mapper)
    {
        return $mapper->mapFromEloquent();
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
