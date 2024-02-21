<?php

declare(strict_types=1);

namespace App;

use App\DataTransferObjects\DealMapper;
use App\Enums\DealPurchaseType;
use App\Jobs\Lender\Deal\DealCreated as JobDealCreated;
use App\Services\QueryServices\Lender\Deals\ForbiddenDeals;
use App\Services\QueryServices\Lender\Deals\GetLendersForDeal as QueryServiceGetLenders;
use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

/**
 * Class Deal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Deal extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;

    public const LOAN_TYPE = [
        1 => 'PURCHASE',
        2 => 'REFINANCE',
        3 => 'CONSTRUCTION',
    ];

    public const PROPERTY_TYPE = [
        1 => 'INVESTMENT',
        2 => 'OWNER_OCCUPIED',
        3 => 'CONSTRUCTION',
    ];

    public const RETAIL = 1;

    public const OFFICE = 2;

    public const INDUSTRIAL = 3;

    public const MIXED_USE = 4;

    public const CONSTRUCTION = 5;

    public const OWNER_OCCUPIED = 6;

    public const QUOTE_LIMIT = 3;

    public const LAND = 7;

    public const MULTIFAMILY = 8;

    public const ASSET_TYPE_CONSTRUCTION = 3;

    public const ASSET_TYPE_OWNER_OCCUPIED = 2;

    public const OPEN = 1;

    public const TERM_SHEET = 2;

    public const UNDERWRITING = 3;

    public const APPROVED = 4;

    public const QUOTE_ACCEPTED = 5;

    public const SAVE_DEAL = 1;

    public const ARCHIVE_DEAL = 2;

    public const IGNORE_DEAL = 3;

    public const PROJECTION_INCREASE_REASONS = [
        1 => 'INCREASED_OCCUPANCY',
        2 => 'BETTER_LEASES',
        3 => 'RENOVATIONS_IMPROVEMENTS',
    ];

    public const SECOND_QUOTE_NOTIFY_FIRST = 1;

    public const SECOND_QUOTE_NOTIFY_SECOND = 2;

    public const INVESTMENT_PURCHASE_REFINANCE = 1;

    public const OWNER_OCCUPIED_PURCHASE_REFINANCE = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'finished_at', 'dollar_amount', 'location', 'sponsor_name', 'main_type',
        'second_quote_accepted_at', 'second_quote_notify', 'purchase_type', 'last_edited',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'finished_at' => 'datetime',
        'premiumed_at' => 'datetime',
        'data' => 'array',
        'purchase_type' => DealPurchaseType::class,
    ];

    /**
     * Column for Soft delete
     */
    /**
     * Array of relation for Cascade Soft Delete and Restore
     */
    protected $cascadeDeletes = ['quotes'];

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    public function assetTypes()
    {
        return $this->belongsToMany(AssetTypes::class, 'deal_asset_type', 'deal_id', 'asset_type_id')
                    ->using(DealAssetType::class);
    }

    public function broker()
    {
        return $this->belongsTo(Broker::class, 'user_id');
    }

    public function emailNotifications()
    {
        return $this->morphMany(EmailNotification::class, 'referenceable');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function unseenQuotes()
    {
        return $this->quotes()
                    ->whereNull('deleted_at')
                    ->where('finished', true)
                    ->where('seen', false);
    }

    public function relatedUsers()
    {
        return $this->hasMany(UserDeals::class);
    }


    public function scopeFinishedForBroker($query, $userId)
    {
        return $query->whereNull('deleted_at')
            ->where('finished', true)
            ->where('user_id', $userId);
    }

    public function scopeLimitedDeals($query)
    {
        return $query->where('purchase_type', DealPurchaseType::NOT_PURCHASED->value);
    }

    /***************************************************************************************
     ** GENERAL
     ***************************************************************************************/

    public function isPremium(): bool
    {
        if ($this->premiumed_at) {
            return true;
        }
        return false;
    }

    public function setPremium(DealPurchaseType $purchaseType): void
    {
        $this->purchase_type = $purchaseType;
        $this->premiumed_at = now();
        $this->save();
    }

    public function quoteLimitReached(): bool
    {
        return $this->quote_limit_reached;
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function mappedDeal()
    {
        try {
            $mapper = new DealMapper($this->id);

            return $mapper->mapFromEloquent();
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            exit;
        }
    }

    /**
     * @param $typeOfLoan
     *
     * Store type of loan for the deal in addition table
     */
    public function storeDealTypeOfLoan($typeOfLoan)
    {
        $dealTypeOfLoan = new DealTypeOfLoan();
        $dealTypeOfLoan->deal_id = $this->id;
        $dealTypeOfLoan->type_of_loan = $typeOfLoan;
        $dealTypeOfLoan->save();
    }

    public function checkIfAlreadyStoredTypeOfLoan($typeOfLoan)
    {
        return DealTypeOfLoan::where('deal_id', $this->id)->where('type_of_loan', $typeOfLoan)->get();
    }

    /**
     * @return array
     *
     * Get type of loan for deal
     */
    public function getTypeOfLoan()
    {
        $dealTypeExist = DealTypeOfLoan::where('deal_id', $this->id)->get();
        if ($dealTypeExist->isNotEmpty()) {
            $dealTypeOfLoans = DealTypeOfLoan::where('deal_id', $this->id)->pluck('type_of_loan')->toArray();

            return array_unique($dealTypeOfLoans);
        }

        return [];
    }

    public function getNamesTypeOfLoans()
    {
        $types = '';
        $dealTypeOfLoans = $this->getTypeOfLoan();
        if (empty($dealTypeOfLoans)) {
            return $types;
        }
        $count = 1;
        foreach ($dealTypeOfLoans as $dealTypeOfLoan) {
            if ($count > 1) {
                $types .= ', ';
            }
            $type = DealTypeOfLoan::DEAL_TYPE_OF_LOAN[$dealTypeOfLoan];
            if ($type === 'Hard Money') {
                $type = 'Hard Money/Bridge';
            }
            $types .= $type;
            $count++;
        }

        return $types;
    }

    /**
     * @param $deal
     * @param $matchedAssetType
     * @param $preferences
     * @return bool
     */
    public function checkMultifamily($deal, $matchedAssetType, $preferences)
    {
        //If there is only multifamily type check amount in two different fields
        $isMultifamily = in_array(Deal::MULTIFAMILY, $matchedAssetType) && count($matchedAssetType) === 1;

        if (! $isMultifamily) {
            return true;
        }

        $lenderMultiAmount = $preferences->getMultifamily();
        if (! $lenderMultiAmount) {
            return true;
        }

        $multiMin = $lenderMultiAmount['min_amount'];
        $multiMax = $lenderMultiAmount['max_amount'];
        $dealMultiAmount = $deal['inducted']['multifamilyAmount'];

        $checkMultifamilyMin = $dealMultiAmount >= $multiMin;
        $checkMultifamilyMax = $dealMultiAmount <= $multiMax;
        if (! $checkMultifamilyMin || ! $checkMultifamilyMax) {
            return false;
        }

        return true;
    }

    /**
     * @param $deal
     * @param $lenders
     * @param $isDomainForbidden
     * @param  array  $forbiddenDomains
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLendersWithDealPreferences($deal, $lenders, $isDomainForbidden, $forbiddenDomains = [])
    {
        $collection = new Collection();

        $domains = [];

        foreach ($lenders as $lender) {
            if (is_int($lender)) {
                $lender = Lender::find($lender);
            }

            if ($lender instanceof stdClass) {
                $lender = Lender::find($lender->id);
            }

            $preferences = $lender->getPerfectFit();

            if (! $preferences) {
                continue;
            }

            if (! $lender->beta_user) {
                continue;
            }

            //Check if this lender
            if ($forbiddenDomains && in_array(preg_replace('/.+@/', '', $lender->email), $forbiddenDomains)) {
                continue;
            }

            $asset_types = $preferences->getAssetTypes() ?? [];
            $loan_size = $preferences->getLoanSize() ?? [];

            // Get forbidden Deals of teams connections
            if (array_key_exists($lender->domain, $domains)) {
                $connectionDealIds = collect();
            } else {
                $forbiddenDeals = resolve(ForbiddenDeals::class);
                $connectionDealIds = $forbiddenDeals->run(['id' => $lender->id, 'domain' => $lender->domain]);
                $domains[$lender->domain] = '';
            }
            // $forbiddenDeals = resolve(ForbiddenDeals::class);
            // $connectionDealIds = $forbiddenDeals->run(['id' => $lender->id, 'domain' => $lender->domain]);

            $finalDealIds = new \Illuminate\Database\Eloquent\Collection();
            if ($isDomainForbidden) {
                // Merge excluded ids based on connections and location
                $finalDealIds = $connectionDealIds; //->merge($finalExcludedIds);
            }

            // If Deal id is in excluded return
            if (in_array($this->id, $finalDealIds->toArray())) {
                continue;
            }

            $perfectLocation = $preferences->getWorkingAreas();
            $excludedAreas = $preferences->getExcludedAreas();
            $isPerfectFitLocation = $this->checkPerfectFitLocation($perfectLocation, $excludedAreas, $deal);

            if (! $isPerfectFitLocation) {
                continue;
            }

            //Type of loans filtered for emails
            $typeOfLoans = $preferences->getTypeOfLoansLender() ?? [];
            $dealTypeOfLoans = $this->getTypeOfLoan();
            // Check if there is matched type of loans on deal
            $matchedTypeOfLoans = array_intersect($dealTypeOfLoans, $typeOfLoans);
            if (! $matchedTypeOfLoans && ! empty($typeOfLoans)) {
                continue;
            }

            // Check Deal dollar amount
            $min = $loan_size['min'];
            $max = $loan_size['max'];

            if ($deal['purchase_loan']['loan_amount'] !== 0) {
                $dollarAmount = $deal['purchase_loan']['loan_amount'];
            } elseif ($deal['construction_loan']['loanAmount'] !== 0) {
                $dollarAmount = $deal['construction_loan']['loanAmount'];
            } else {
                $dollarAmount = $deal['refinance_loan']['loanAmount'];
            }

            $checkMin = $dollarAmount >= $min;
            $checkMax = $dollarAmount <= $max;
            if (! $checkMin || ! $checkMax) {
                continue;
            }

            // If Deal is asset type as Lender preferences
            $dealAssetType = $this->assetTypes()->pluck('asset_type_id')->toArray();
            $matchedAssetType = array_intersect($dealAssetType, $asset_types);

            if (! $matchedAssetType) {
                continue;
            }

            // If there is only multifamily type check amount
            $checkMultifamily = $this->checkMultifamily($deal, $matchedAssetType, $preferences);

            if (! $checkMultifamily) {
                continue;
            }

            // Push model into empty collection
            $collection->push($lender);
        }

        return $collection;
    }

    /**
     * When Deal is published send it to Lenders that are connected to Broker
     * and to Lenders with not same domain and deal preferences
     */
    public function sendDealCreated()
    {
        // Get Broker author of Deal
        $broker = $this->broker()->first();

        // Get Broker's connections
        $connectedLenders = $broker->lenders()->whereNotNull(DB::raw("metas::jsonb->'perfect_fit'"))->cursor();

        // Map Deal info
        $deal = $this->mappedDeal();

        $forbiddenDomains = [];

        //Check connected lenders preferences
        if (! $connectedLenders->isEmpty()) {
            $checkConnectedLenders = $this->getLendersWithDealPreferences($deal, $connectedLenders, false);

            if (! $checkConnectedLenders->isEmpty()) {
                $inc = 0;
                $arrOfEmailLender1 = [];
                foreach ($checkConnectedLenders as $connectedLender) {
                    $inc++;
                    $arrOfEmailLender1[] = $connectedLender->email;
                    JobDealCreated::dispatch($connectedLender, $deal, true, $inc, count($checkConnectedLenders), $arrOfEmailLender1, 'connected');
                    $connectedLender->storeRelationUserDeal($this->id, User::LENDER_DEAL_PUBLISHED);
                    $forbiddenDomains[] = preg_replace('/.+@/', '', $connectedLender->email);
                }
            }
        }

        // Get lenders based on deal preferences
        $dealDollarAmount = (int) $deal['inducted']['loan_amount'];

        $dealLocations = [
            $deal['location']['state'],
            $deal['location']['city'],
            $deal['location']['sublocality'],
            $deal['location']['county'],
            $deal['location']['country'],
        ];

        $dealAssetTypes = $deal['inducted']['property_type']['asset_types'];

        $queryServiceGetLenders = resolve(QueryServiceGetLenders::class);

        $allLenders = $queryServiceGetLenders->query($dealDollarAmount, $dealLocations, $dealAssetTypes)->cursor()->unique('id');

        $lendersBasedOnPreferences = $this->getLendersWithDealPreferences($deal, $allLenders, false, $forbiddenDomains);

        // Check if there is connected lenders and with that deal preferences
        if ($lendersBasedOnPreferences->isEmpty()) {
            return;
        }

        // For every Lender send them email
        $i = 0;
        $non_beta = 0;
        $arrOfEmailLender = [];

        foreach ($lendersBasedOnPreferences as $lender) {
            $arrOfEmailLender[] = $lender->email;
            $i++;
            JobDealCreated::dispatch($lender, $deal, false, $i, count($lendersBasedOnPreferences), $arrOfEmailLender, 'publish');
        }
    }

    /**
     * @param $dealId
     * @param $args
     * @return string
     */
    public function checkDealFlow($dealId, $args): string
    {
        $dataType = '';

        $deal = Deal::find($dealId);
        $dealMapped = $deal->mappedDeal();

        if (isset($dealMapped['loan_type']) && isset($args['loan_type']) && $dealMapped['loan_type'] !== $args['loan_type']) {
            $dataType = 'loan_type';
        } elseif (isset($dealMapped['property_type']) && isset($args['property_type']) && $dealMapped['property_type'] !== $args['property_type']) {
            $dataType = 'property_type';
        } elseif (isset($dealMapped['existing']['propertyType']) && isset($args['existing']['propertyType']) && $dealMapped['existing']['propertyType'] !== $args['existing']['propertyType']) {
            $dataType = 'property_type';
        } elseif (isset($dealMapped['investment_details']['propType']) && isset($args['investment_details']['propType']) && $dealMapped['investment_details']['propType'] !== $args['investment_details']['propType']) {
            $dataType = 'investment_type';
        } elseif (isset($dealMapped['type_of_loans']) && ! empty($dealMapped['type_of_loans']) && (
            (isset($args['construction_loan']['loanAmount']) && $args['construction_loan']['loanAmount'])
                || (isset($args['refinance_loan']['loanAmount']) && $args['refinance_loan']['loanAmount']) ||
            (isset($args['purchase_loan']['loan_amount']) && $args['purchase_loan']['loan_amount'])
        )) {
            $dataType = 'loan_amount';
        }

        return $dataType;
    }

    /**
     * @param $relation_type
     * @return HasMany
     */
    public function checkUserDeal($relation_type)
    {
        return $this->relatedUsers()->where('relation_type', $relation_type)->where('deal_id', $this->id);
    }

    /**
     * @param $perfectLocation
     * @param $excludedAreas
     * @param $deal
     * @return bool
     */
    public function checkPerfectFitLocation($perfectLocation, $excludedAreas, $deal): bool
    {
        $checkStates = array_filter(array_map(function ($location) use ($deal, $excludedAreas) {
            if ($location['long_name'] === 'United States' && empty($excludedAreas)) {
                return true;
            }
            if ($location['long_name'] === 'United States' && ! empty($excludedAreas)) {
                return $location;
            }
            $checkedPerfect = $this->mapThroughLocations($location, $deal);
            if ($checkedPerfect) {
                return $location;
            }

            return false;
        }, $perfectLocation), function ($item) {
            return $item !== null && $item !== false && $item !== '';
        });

        //If state doesn't match
        if (empty($checkStates)) {
            return false;
        }
        //If chosen location is USA and there is no excluded area return true
        if (in_array(true, $checkStates) && empty($excludedAreas)) {
            return true;
        }

        //Check match in $checkStates from $excludedAreas based on state
        $checkExcludedStatesInWorking = array_filter(array_map(function ($excludedArea) use ($checkStates) {
            $checkedMatch = $this->compareStates($excludedArea, $checkStates);
            if ($checkedMatch) {
                return $excludedArea;
            }

            return false;
        }, $excludedAreas), function ($item) {
            return $item !== null && $item !== false && $item !== '';
        });
        //If there is no excluded state in working state return true
        if (empty($checkExcludedStatesInWorking)) {
            return true;
        } else {
            $excludedAreas = $checkExcludedStatesInWorking;
        }

        $checkExcludedStates = array_filter(array_map(function ($location) use ($deal) {
            $checkedExcluded = $this->mapThroughLocations($location, $deal, true);
            if ($checkedExcluded) {
                return $location;
            }

            return false;
        }, $excludedAreas), function ($item) {
            return $item == null && $item == false && $item == ''; //Return only false
        });

        //If there is no excluded area return true
        if (empty($checkExcludedStates)) { //If there is not false return true
            return true;
        }

        return false;
    }

    /**
     * @param $location
     * @param $deal
     * @param  bool  $isExcluded
     * @return bool
     */
    private function mapThroughLocations($location, $deal, $isExcluded = false): bool
    {
        $checkCounty = true;
        if ($location['state'] && $location['city']) {
            $checkCounty = false;
        }
        $checked = [];
        if ($location['state']) {
            $checked[] = $deal['location']['state'] === $location['state'];
        }
        if ($location['county'] && $checkCounty) {
            $checked[] = $deal['location']['county'] === $location['county'];
        }
        if ($location['city'] && $deal['location']['city']) {
            $checked[] = $deal['location']['city'] === $location['city'];
        }
        if ($location['sublocality'] && $deal['location']['sublocality']) {
            $checked[] = $deal['location']['sublocality'] === $location['sublocality'];
        }
        if ($location['zip_code']) {
            $checked[] = $deal['location']['zip_code'] === $location['zip_code'];
        }

        $checkLocation = in_array(false, $checked);
        if ($checkLocation && ! $isExcluded) {
            return false;
        }
        //if true there is false and not matched for excluded
        if ($checkLocation && $isExcluded) {
            return true;
        }
        //If false then is matched for excluded
        if (! $checkLocation && $isExcluded) {//if true there is false
            return false;
        }

        return true;
    }

    /**
     * @param $excludedArea
     * @param $checkStates
     * @return bool
     */
    private function compareStates($excludedArea, $checkStates): bool
    {
        $checkedExcluded = [];
        foreach ($checkStates as $state) {
            if ($state['state'] === $excludedArea['state']) {
                $checkedExcluded[] = true;
            }
            if (! $state['state'] && $state['long_name'] === 'United States') {
                $checkedExcluded[] = true;
            }
        }
        $checkLocation = in_array(true, $checkedExcluded);
        if ($checkLocation) {
            return true;
        }

        return false;
    }

    /***************************************************************************************
     ** GETTERS
     ***************************************************************************************/

    public function getLoanType(): ?int
    {
        return data_get($this->data, 'loan_type');
    }

    /**
     * @return string
     */
    public function getDealType(): string
    {
        return $this->purchase_type->dealType();
    }

    public function hasBeenPurchasedByUser($user)
    {
        return Payment::where('deal_id', $this->id)
            ->where('user_id', $user->id)
            ->where('payment_status', Payment::STATUS_PAID)
            ->exists();
    }

    public function getDollarAmount()
    {
        $loanType = data_get($this->data, 'loan_type');

        if ($loanType === 1) {
            return data_get($this->data, 'purchase_loan.loan_amount');
        }
        if ($loanType === 2) {
            return data_get($this->data, 'refinance_loan.loanAmount');
        }
        if ($loanType === 3) {
            return data_get($this->data, 'construction_loan.loanAmount');
        }
    }

    public function buildAddress()
    {
        return $this->data['location']['street_address']
            . ", ". $this->data['location']['city']
            . ", ". $this->data['location']['state']
            . ", " . $this->data['location']['zip_code'];
    }
}
