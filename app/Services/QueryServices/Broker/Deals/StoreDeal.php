<?php

namespace App\Services\QueryServices\Broker\Deals;

use App\Deal;
use App\Events\DealPublished;
use Illuminate\Support\Facades\Log;

class StoreDeal extends \App\Services\QueryServices\AbstractQueryService
{
    /**
     * @64Robots
     * Note: Unclear if or when this is called. This does not seem to be called
     * when I publish a deal in the regular workflow as a broker.
     */
    public function run(array $args)
    {
        $dealId = isset($args['request_args']['dealId']) ? $args['request_args']['dealId'] : false;
        $userStatus = strtolower($args['user']->getCompany()['company_status']);
        $finishApproved = true;
        if (isset($args['request_args']['finished'])) {
            if ($userStatus != 'approved') {
                $finishApproved = false;
            }
        }
        if (! $finishApproved) {
            throw new \Exception('Denied, User or Company not approved to finish saving Deal', 403);
        }
        $deal = $dealId ? Deal::find($args['request_args']['dealId']) : new Deal();
        try {
            $mapper = new \App\DataTransferObjects\DealMapper($deal);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            throw new \Exception($e->getMessage(), 400);
        }

        /**
         * Deal Mapper
         */
        $deal = $mapper->mapToEloquent($args['request_args'], $args['user']);
        if ($dealId) {
            // Check if deal flow is changed
            $dataType = $deal->checkDealFlow($dealId, $args['request_args']);
            // If flow is changed reset data for the rest of the form
            if ($dataType) {
                $deal = $mapper->resetData($dataType);
            }
        }

        $deal->save();

        if ($deal->finished) {
            $deal->update(['finished_at' => now()]);
            DealPublished::dispatch($deal);
        }

        return $deal;
    }
}
