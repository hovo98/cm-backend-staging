<?php

namespace App\Actions;

use App\Deal;
use Illuminate\Support\Collection;

class DealGetLenderWithPreferences
{
    public Deal $deal;

    public array $mappedDeal;

    public Collection $lenders;

    public bool $isDomainForbidden;

    public array $forbiddenDomains;

    /**
     * @param $deal
     * @param $lenders
     * @param $isDomainForbidden
     * @param  array  $forbiddenDomains
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function __construct(Deal $deal, array $mappedDeal, $lenders, bool $isDomainForbidden, array $forbiddenDomains = [])
    {
        $this->deal = $deal;
        $this->mappedDeal = $mappedDeal;
        $this->lenders = $lenders instanceof Collection ? $lenders : collect($lenders); // array of ids or stdClasses
        $this->isDomainForbidden = $isDomainForbidden;
        $this->forbiddenDomains = $forbiddenDomains;
    }

    public function __invoke()
    {
        $collection = collect([]);

        $domains = [];

        $lenders = $this->getLenders();

        foreach ($lenders as $lender) {
            $preferences = $lender->getPerfectFit();

            if ($this->shouldSkip($lender, $preferences) || $this->lenderDomainIsBlocked($lender, $forbiddenDomains)) {
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
        }
    }

    public function getLenders(): Collection
    {
        $lenderIds = $this->lenders->map(function ($value) {
            if (is_int($value)) {
                return $value;
            }
            if ($value instanceof stdClass) {
                return $value->id;
            }

            return $value->id; // assume this is the model
        })->toArray();

        return Lender::whereIn('id', $ids)->get();
    }

    public function shouldSkip(Lender $lender, $preferences): bool
    {
        if (! $preferences) {
            return true;
        }
        if (! $lender->beta_user) {
            return true;
        }

        return false;
    }

    public function lenderDomainIsBlocked(Lender $lender, array $forbiddenDomains): bool
    {
        if ($forbiddenDomains && in_array(preg_replace('/.+@/', '', $lender->email), $forbiddenDomains)) {
            return true;
        }

        return false;
    }
}
