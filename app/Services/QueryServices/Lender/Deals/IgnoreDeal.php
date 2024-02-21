<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Deal;
use App\Jobs\Lender\Deal\DealCreated as JobDealCreated;
use App\Lender;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;
use App\Services\QueryServices\Lender\SameDomainLenders;
use App\User;

/**
 * Class IgnoreDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IgnoreDeal extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['user'];
        $deal_id = $args['deal_id'];

        // Check role
        if ($user->role !== 'lender') {
            return [
                'status' => false,
                'message' => 'Only lender can skip a deal.',
            ];
        }

        // Get Deal
        $deal = Deal::find($deal_id);
        if (! $deal) {
            return [
                'status' => false,
                'message' => 'This Deal doesn\'t exist.',
            ];
        }

        //Check if lender quoted Deal
        $dealCountQuotes = Quote::where('user_id', $user->id)->where('deal_id', $deal->id)->where('finished', true)->count();
        if ($dealCountQuotes > 0) {
            return [
                'status' => false,
                'message' => 'You already quoted this deal.',
            ];
        }
        $dealMapped = $deal->mappedDeal();

        //Check if this deal is already ignored
        $checkIgnoredDeal = $user->checkRelatedDeal($deal_id, User::LENDER_IGNORE_DEAL);
        if ($checkIgnoredDeal->isNotEmpty()) {
            return [
                'status' => false,
                'message' => 'You already skipped this deal.',
            ];
        }
        $checkDealPublished = $user->checkRelatedDeal($deal_id, User::LENDER_DEAL_PUBLISHED);
        if ($checkDealPublished->isNotEmpty()) {
            $user->removeRelation($deal_id, User::LENDER_DEAL_PUBLISHED);
        }

        // Store relation
        $user->storeRelationUserDeal($deal_id, User::LENDER_IGNORE_DEAL);

        // Get lenders with the same domain and skip this lender
        $lender = Lender::find($user->id);
        $sameDomainLenders = resolve(SameDomainLenders::class);
        $lendersWithSameDomain = $sameDomainLenders->run(['id' => $lender->id, 'domain' => preg_replace('/.+@/', '', $lender->email)]);
        if ($lendersWithSameDomain->isEmpty()) {
            return [
                'status' => false,
                'message' => 'You skipped this deal.',
            ];
        }

        // Get Lenders with that domain and deal preferences
        $lendersBasedOnPreferences = $deal->getLendersWithDealPreferences($dealMapped, $lendersWithSameDomain, true);
        // If there is lenders with this criteria send them deal in email
        if ($lendersBasedOnPreferences->isNotEmpty()) {
            // For every Lender send email
            $i = 0;
            $arrOfEmailLender = [];
            foreach ($lendersBasedOnPreferences as $lender) {
                $i++;
                $arrOfEmailLender[] = $lender->email;
                JobDealCreated::dispatch($lender, $dealMapped, false, $i, count($lendersBasedOnPreferences), $arrOfEmailLender);
            }
        }

        return [
            'status' => true,
            'message' => 'You skipped this deal.',
        ];
    }
}
