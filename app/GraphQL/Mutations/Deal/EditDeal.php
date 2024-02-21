<?php

declare(strict_types=0);

namespace App\GraphQL\Mutations\Deal;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\DealAssetType;
use App\DealTypeOfLoan;
use App\Quote;
use App\UserDeals;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class EditDeal
 *
 *
 * @author  Hajdi Djukic Grba <hajdi@forwardslashny.com>
 */
class EditDeal
{
    /**
     * @param    $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $dealId = (int) $args['id'];
        $user = $context->user();
        $deal = Deal::find($dealId);

        if ($deal->last_edited === null) {
            //If the deal was not yet published
            $hoursBetweenEdits = 24;
        } else {
            //Gettings the hours difference between updated_at timestamp and current timestamp.
            $hoursDifference = now()->diff($deal->last_edited);

            //Converting the difference to the hours that have passed from the last edit
            $hoursBetweenEdits = ($hoursDifference->days * 24) + $hoursDifference->h;
        }

        if ($deal->finished && $deal->main_type === 5) {
            return [
                'success' => true,
                'message' => 'Deal can\'t be changed',
                'finished' => $deal->finished,
            ];
        } elseif ($deal->finished && $deal->main_type !== 5 && $hoursBetweenEdits >= 24) {
            $quotes = Quote::where('deal_id', $deal->id)->get();

            foreach ($quotes as $quote) {
                $quote->deleted_by = 1;
                $quote->save();
            }

            Quote::where('deal_id', $deal->id)->delete();

            $userDeal = UserDeals::where('deal_id', $deal->id);
            $userDeal->forceDelete();

            $dealAssetType = DealAssetType::where('deal_id', $deal->id);
            $dealAssetType->forceDelete();
            $dealTypeOfLoans = DealTypeOfLoan::where('deal_id', $deal->id);
            $dealTypeOfLoans->forceDelete();

            $mapper = new DealMapper($deal->id);
            $mappedDeal = $mapper->mapFromEloquent();

            $mappedDeal['finished'] = false;

            if (isset($mappedDeal['lastStepStatus'])) {
                unset($mappedDeal['lastStepStatus']);
            }

            $deal = $mapper->mapToEloquent($mappedDeal, $user);

            $deal->currently_editing = 1;
            $deal->finished = false;
            $deal->save();
        }

        return [
            'success' => true,
            'message' => 'Deal is drafted.',
            'finished' => $deal->finished,
        ];
    }
}
