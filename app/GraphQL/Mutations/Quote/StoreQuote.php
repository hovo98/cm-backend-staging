<?php

namespace App\GraphQL\Mutations\Quote;

use App\DataTransferObjects\QuoteMapper;
use App\Events\QuoteChanged;
use App\Events\QuotePublished;
use App\Quote;
use App\User;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class StoreQuote
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();

        $quoteID = $args['id'] ?? false;
        $dealID = $args['deal']['id'];

        // Check if quote is already finished
        if ($quoteID) {
            $quote = Quote::query()->find($quoteID, ['finished']);

            if ($quote->finished) {
                throw new Error('This quote has been published and cannot be edited');
            }
        }

        //Check if it's new quote
        if (! $quoteID) {
            // Get all quotes for this deal from this lender and count
            $dealsCount = Quote::where('user_id', $user->id)->where('deal_id', $dealID)->where('finished', true)->count();
            // Count quotes
            $quotesFromLender = $dealsCount;

            // If there is already 3 quotes return
            if ($quotesFromLender >= 3) {
                throw new Error('Limit is 3 quotes per deal');
            }
        }

        try {
            $mapper = new \App\DataTransferObjects\QuoteMapper($quoteID);
        } catch (\Exception $e) {
            //            Log::warning('JsonbMapper cant be created');
            Log::warning($e->getMessage());
            exit;
        }
        $ifConstructionTermNewArgs = $args;
        if ($quoteID) {
            // Check if quote flow is changed
            $dataType = $this->checkQuoteFlow($quoteID, $args);

            // If flow is changed reset data for the rest of the form
            if ($dataType) {
                $quoteReset = $mapper->resetData($dataType);
                $quoteReset->save();
            }
            $ifConstructionTermNewArgs = $this->checkConstructionTerm($args);
        }

        $quote = $mapper->mapToEloquent($ifConstructionTermNewArgs, $user);
        $quote->deal_id = $dealID;
        $quote->save();

        if ($quote->finished) {
            $quote->update(['finished_at' => now()]);
            //Update columns when quote is finished
            $quoteMapped = $mapper->mapFromEloquent($quote);
            $quote->update(['dollar_amount' => $quoteMapped['inducted']['dollar_amount']]);

            if ($quoteMapped['inducted']['interest_rate']) {
                $quote->update(['interest_rate' => $quoteMapped['inducted']['interest_rate']]);
            }
            if ($quoteMapped['inducted']['interest_rate_spread']) {
                $quote->update(['interest_rate_spread' => $quoteMapped['inducted']['interest_rate_spread']]);
            }
            if ($quoteMapped['inducted']['origFeePercent']) {
                $quote->update(['origination_fee_spread' => $quoteMapped['inducted']['origFeePercent']]);
            }
            if ($quoteMapped['inducted']['interest_rate_float']) {
                $quote->update(['interest_rate_float' => $quoteMapped['inducted']['interest_rate_float']]);
            }
            $quote->update(['rate_term' => $quoteMapped['inducted']['rate_term']]);
            $quote->update(['origination_fee' => $quoteMapped['inducted']['origFee']]);
            $quote->update(['interest_swap' => $quoteMapped['inducted']['interest_swap']]);

            QuotePublished::dispatch($quote);
            event(new QuoteChanged($quote, 'createdPublished'));

            $this->checkIfHasRelationship($user, $quote->deal_id);

            //Check if Lender has draft quotes for this deal and delete them
            $unfinishedQuotes = Quote::where('user_id', $user->id)->where('deal_id', $dealID)->where('finished', false)->get();
            foreach ($unfinishedQuotes as $unfinishedQuote) {
                $unfinishedQuote->forceDelete();
            }
        }

        return  $mapper->mapFromEloquent();
    }

    protected function checkQuoteFlow($quoteID, $args): string
    {
        $dataType = '';
        try {
            $mapper = new QuoteMapper($quoteID);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            exit;
        }
        $quoteMapped = $mapper->mapFromEloquent();

        if (isset($quoteMapped['constructionLoans']['permanentLoanOptionType']) && isset($args['constructionLoans']['permanentLoanOptionType'])
            && $quoteMapped['constructionLoans']['permanentLoanOptionType'] !== 0 &&
            $quoteMapped['constructionLoans']['permanentLoanOptionType'] !== $args['constructionLoans']['permanentLoanOptionType']) {
            $dataType = 'permanentLoanOptionType';
        }

        return $dataType;
    }

    /**
     * @param $args
     * @return mixed
     *
     * Convert construction term from months into years
     */
    private function checkConstructionTerm($args)
    {
        if (isset($args['constructionLoans']) && isset($args['constructionLoans']['constructionTerm']) && $args['constructionLoans']['constructionTerm'] !== '') {
            $constructionTerm = (int) $args['constructionLoans']['constructionTerm'];
            $converted = round($constructionTerm / 12, 1);
            $year = $converted > 1 ? 'years' : 'year';
            $args['constructionLoans']['constructionTerm'] = (string) $converted.' '.$year;
        }

        return $args;
    }

    private function checkIfHasRelationship($lender, $dealId)
    {
        $relatedDealsPublished = $lender->checkRelatedDeal($dealId, User::LENDER_DEAL_PUBLISHED);
        if ($relatedDealsPublished->isNotEmpty()) {
            $lender->removeRelation($dealId, User::LENDER_DEAL_PUBLISHED);
        }
    }
}
