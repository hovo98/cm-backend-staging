<?php

namespace App\Jobs;

use App\DataTransferObjects\Fit;
use App\DataTransferObjects\LoanSize;
use App\Deal;
use App\Jobs\Lender\Deal\DealCreated as JobDealCreated;
use App\Lender;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class LenderNotifyOfDealIfInterested implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Lender $lender;

    public Deal $deal;

    public array $mappedDeal;

    public bool $isDomainForbidden = false;

    public array $forbiddenDomains = [];

    public string $relation;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Lender $lender, Deal $deal, array $mappedDeal, string $relation = 'connected')
    {
        $this->lender = $lender;
        $this->deal = $deal;
        $this->mappedDeal = $mappedDeal;
        $this->relation = $relation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->lenderShouldRecieveNotification()) {
            JobDealCreated::dispatch(
                $this->lender,
                $this->mappedDeal,
                $this->isConnected(),
                1, // placeholder
                1, // placeholder
                [$this->lender->email],
                $this->relation
            );

            // Connected Lenders Get Preference for the deal
            if ($this->relation === 'connected') {
                $this->lender->storeRelationUserDeal($this->deal->id, User::LENDER_DEAL_PUBLISHED);
            }
        }
    }



    public function lenderShouldRecieveNotification(): bool
    {
        $preferences = $this->lender->getPerfectFit();
        if (! $this->isPerfectFitLocation($preferences)) {
            return false;
        }

        if (! $this->loanTypeMatches($preferences)) {
            return false;
        }

        $loanSize = $preferences->getLoanSize() ?? [];
        if (! $this->dollarAmountMatches($loanSize)) {
            return false;
        }

        if (! $this->multiFamilyCheckPasses($preferences)) {
            return false;
        }

        // matches completely
        return true;
    }

    /***************************************************************************************
     ** VALIDATE MATCH
     ***************************************************************************************/

    public function isPerfectFitLocation(Fit $preferences): bool
    {
        $perfectLocation = $preferences->getWorkingAreas();
        $excludedAreas = $preferences->getExcludedAreas();
        $isPerfectFitLocation = $this->deal->checkPerfectFitLocation($perfectLocation, $excludedAreas, $this->mappedDeal);
        if (! $isPerfectFitLocation) {
            return false;
        }

        return true;
    }

    public function loanTypeMatches(Fit $preferences): bool
    {
        $typeOfLoans = $preferences->getTypeOfLoansLender() ?? [];
        $dealTypeOfLoans = $this->deal->getTypeOfLoan();

        // Check if there is matched type of loans on deal
        $matchedTypeOfLoans = array_intersect($dealTypeOfLoans, $typeOfLoans);
        if (! $matchedTypeOfLoans && ! empty($typeOfLoans)) {
            return false;
        }

        return true;
    }

    public function dollarAmountMatches(LoanSize $loanSize): bool
    {
        $min = $loanSize['min'];
        $max = $loanSize['max'];

        $dollarAmount = $this->getDollarAmount();

        $checkMin = $dollarAmount >= $min;
        $checkMax = $dollarAmount <= $max;
        if (! $checkMin || ! $checkMax) {
            return false;
        }

        return true;
    }

    public function multiFamilyCheckPasses(Fit $preferences): bool
    {
        $matchingAssetTypes = $this->getMatchingAssetTypes($preferences);

        $checkMultifamily = $this->deal->checkMultifamily($this->mappedDeal, $matchingAssetTypes, $preferences);
        if (! $checkMultifamily) {
            return false;
        }

        return true;
    }

    /***************************************************************************************
     ** HELPERS
     ***************************************************************************************/

    public function getMatchingAssetTypes(Fit $preferences): ?array
    {
        $assetTypes = $preferences->getAssetTypes() ?? [];

        $dealAssetType = $this->deal->assetTypes()->pluck('asset_type_id')->toArray();

        return array_intersect($dealAssetType, $assetTypes);
    }

    public function getDollarAmount(): int
    {
        if (Arr::get($this->mappedDeal, 'purchase_loan.loan_amount')) {
            return Arr::get($this->mappedDeal, 'purchase_loan.loan_amount');
        }
        if (Arr::get($this->mappedDeal, 'construction_loan.loanAmount')) {
            return Arr::get($this->mappedDeal, 'construction_loan.loanAmount');
        }
        if (Arr::get($this->mappedDeal, 'refinance_loan.loanAmount')) {
            return Arr::get($this->mappedDeal, 'refinance_loan.loanAmount');
        }
    }

    public function isConnected(): bool
    {
        return $this->relation === 'connected';
    }
}
