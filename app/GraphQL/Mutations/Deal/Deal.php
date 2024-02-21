<?php

namespace App\GraphQL\Mutations\Deal;

use App\Broker;
use App\DataTransferObjects\DealMapper;
use App\Deal as DealEloquent;
use App\Quote;
use App\Services\QueryServices\Lender\Deals\CheckForbiddenDeals as QueryServiceLender;
use App\Termsheet;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Deal
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
        $status = true;
        $dealEloquent = DealEloquent::find($args['id']);
        $termsheetStatus = false;
        $isSkipped = false;
        $finished_at_individual = '';
        $name = '';
        $quoted = false;

        if (! $dealEloquent) {
            return '';
        }

        if ($dealEloquent->last_edited === null) {
            //If the deal was not yet published
            $hoursBetweenEdits = 24;
        } else {
            //Gettings the hours difference between updated_at timestamp and current timestamp.
            $hoursDifference = now()->diff($dealEloquent->last_edited);

            //Converting the difference to the hours that have passed from the last edit
            $hoursBetweenEdits = ($hoursDifference->days * 24) + $hoursDifference->h;
        }

        if ($hoursBetweenEdits < 24) {
            $limitPassed = false;
        } else {
            $limitPassed = true;
        }

        $untilNextEdit = 24 - $hoursBetweenEdits;

        //If number 0 is greater than untilNextEdit that means that the limit has passed and that the user can edit the deal
        if ($untilNextEdit < 0) {
            $untilNextEdit = 0;
        }

        $mapper = new DealMapper($args['id']);
        $dealMapped = $mapper->mapFromEloquent();
        $terms = Termsheet::all()->toArray();

        if ($user->role === 'broker' && $dealEloquent->user_id !== $user->id) {
            $status = false;
            $terms = [];
            $mapper = new DealMapper();
            $dealMapped = $mapper->getEmptyDeal();
            $termsheetStatus = false;
            $isSkipped = false;
            $finished_at_individual = '';
        }

        if ($user->role === 'lender' && $dealEloquent->finished !== true) {
            $status = false;
            $terms = [];
            $mapper = new DealMapper();
            $dealMapped = $mapper->getEmptyDeal();
            $termsheetStatus = false;
            $isSkipped = false;
            $finished_at_individual = '';
        }

        if ($user->role === 'lender' && $dealEloquent->finished === true) {
            //Check if can see the deal
            $isForbiddenDeal = $this->checkIfForbiddenDeal($user, $args['id']);
            if ($isForbiddenDeal) {
                $status = false;
                $terms = [];
                $mapper = new DealMapper();
                $dealMapped = $mapper->getEmptyDeal();
                $termsheetStatus = false;
                $isSkipped = false;
                $finished_at_individual = '';
            }
        }

        if ($user->role === 'broker' && $dealEloquent->termsheet !== DealEloquent::OPEN) {
            $termsheetStatus = true;
        }

        if ($user->role === 'lender') {
            $isSkipped = $this->checkSkipped($user, $dealEloquent);
            $dealMapped['show_address'] = $this->checkShowAddress($dealMapped);
            $dealMapped = ! $dealMapped['show_address'] ? $this->hideTenants($dealMapped) : $dealMapped;

            $dealCountQuotes = Quote::where('user_id', $user->id)->where('deal_id', $dealEloquent->id)->where('finished', true)->count();
            if ($dealCountQuotes > 0) {
                $quoteStatusCheck = Quote::where('user_id', $user->id)->where('deal_id', $dealEloquent->id)->where('status', 2)->count();
                $dealOwner = $dealEloquent->user_id;
                $brokerUser = User::find($dealOwner);
                if ($quoteStatusCheck > 0) {
                    $name = 'Messages with '.$brokerUser->first_name.' '.$brokerUser->last_name;
                } else {
                    $companyName = $brokerUser->getCompanyNameFromMetasOrFromCompanyRelationship();
                    $name = 'Messages with '.$companyName;
                }
            }

            $isQuoted = Quote::where('deal_id', $dealEloquent->id)->where('finished', true)->count();
            if ($isQuoted > 0) {
                $isQuotedAccepted = Quote::where('deal_id', $dealEloquent->id)->where('status', 2)->count();
                if ($isQuotedAccepted > 0) {
                    $quoted = true;
                }
            }
        }

        if ($user->role === 'broker') {
            $dealMapped['show_address'] = $this->checkShowAddress($dealMapped);
            $dealMapped['is_premium'] = $dealEloquent->isPremium();
            $dealMapped['deal_type'] = $dealEloquent->getDealType();
        }

        $finished_at_individual = $this->dealDateIndividual($dealEloquent, $user);

        return [
            'deal' => $dealMapped,
            'terms' => $terms,
            'status' => $status,
            'termsheet_status' => $termsheetStatus,
            'is_skipped' => $isSkipped,
            'finished_at_individual' => $finished_at_individual,
            'name' => $name,
            'quoted' => $quoted,
            'deal_editable' => $limitPassed,
            'until_next_edit' => $untilNextEdit,
        ];
    }

    /**
     * @param $user
     * @param $dealId
     * @return bool
     */
    private function checkIfForbiddenDeal($user, $dealId): bool
    {
        $forbiddenDeal = false;
        $queryServiceLender = resolve(QueryServiceLender::class);
        //Get all available deals
        $availableDeals = $queryServiceLender->run(['user' => $user]);
        //Get deal if it's available
        $availableDeal = $availableDeals->where('deals.id', $dealId)->get();

        if ($availableDeal->isEmpty()) {
            $forbiddenDeal = true;
        }

        return $forbiddenDeal;
    }

    /**
     * @param $user
     * @param $dealEloquent
     * @return bool
     */
    private function checkSkipped($user, $dealEloquent): bool
    {
        $isSkipped = false;
        $dealMapped = $dealEloquent->mappedDeal();
        // Check if already skipped
        $checkIgnoredDeal = $user->checkRelatedDeal($dealEloquent->id, User::LENDER_IGNORE_DEAL);
        if ($checkIgnoredDeal->isNotEmpty()) {
            return $isSkipped;
        }

        // Check if this lender is connected to broker
        $broker = Broker::find($dealEloquent->user_id);
        $isConnected = $broker->lenders()->where('lender_id', $user->id)->first();
        if (! $isConnected) {
            return $isSkipped;
        }

        //Check if lender quoted Deal
        $dealCountQuotes = Quote::where('user_id', $user->id)->where('deal_id', $dealEloquent->id)->where('finished', true)->count();
        if ($dealCountQuotes > 0) {
            return $isSkipped;
        }

        // Check perfect fit
        $collection = new \Illuminate\Database\Eloquent\Collection();
        $lender = $collection->push($isConnected);
        $checkConnectedLenders = $dealEloquent->getLendersWithDealPreferences($dealMapped, $lender, false);
        if (! $checkConnectedLenders->isEmpty()) {
            $isSkipped = true;
        }

        return $isSkipped;
    }

    /**
     * @param $dealEloquent
     * @param $user
     * @return mixed
     */
    private function dealDateIndividual($dealEloquent, $user)
    {
        //Format time
        return $dealEloquent->finished_at ? $dealEloquent->finished_at->setTimezone($user->timezone)->format('m-d-Y') : $dealEloquent->updated_at->setTimezone($user->timezone)->format('m-d-Y');
    }

    /**
     * @param $mappedDeal
     * @return bool
     */
    private function checkShowAddress($mappedDeal): bool
    {
        $loan_type = $mappedDeal['inducted']['loan_type'];

        if ($loan_type === 1) {
            return $mappedDeal['show_address_purchase'] === 'true' ? false : true;
        } elseif ($loan_type === 3) {
            return $mappedDeal['construction_loan']['show_address_construction'] === 'true' ? false : true;
        } else {
            return true;
        }
    }

    /**
     * @param $dealMapped
     * @return array
     */
    private function hideTenants($dealMapped): array
    {
        $table = $dealMapped['rent_roll']['table'];
        $dealMapped['rent_roll']['table'] = array_map(function ($var) {
            $var['name'] = '';

            return $var;
        }, $table);

        return $dealMapped;
    }
}
