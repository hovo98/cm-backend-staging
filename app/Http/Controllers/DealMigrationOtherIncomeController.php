<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class DealMigrationOtherIncomeController
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DealMigrationOtherIncomeController extends Controller
{
    /**
     * @var Collection
     */
    private $deals;

    /**
     * Change structure for other expenses field
     */
    public function updateOtherIncome()
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
        $this->deals
            ->each(function (Deal $deal) {
                // Prepare data
                $mapper = $this->getMapper($deal);
                $mappedDeal = $this->getMappedDeal($mapper);
                $user = $this->getUser($deal);

                // Add new field
                if (! isset($mappedDeal['rent_roll']['other_income'])) {
                    if (isset($mappedDeal['lastStepStatus'])) {
                        unset($mappedDeal['lastStepStatus']);
                    }
                    $mappedDeal['rent_roll']['other_income'] = $this->updateValue();
                }

                // Persist data
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
     * Add new field
     *
     * @return array[]
     */
    private function updateValue(): array
    {
        return [
            [
                'type' => '',
                'amount' => '',
            ],
        ];
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
