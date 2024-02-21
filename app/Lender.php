<?php

declare(strict_types=1);

namespace App;

use App\DataTransferObjects\Fit;
use App\Jobs\Admin\SuspiciousLocations as SuspiciousLocationsJob;
use App\Mail\ErrorEmail;
use App\Notifications\WelcomeEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Class Lender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Lender extends User
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var string[]
     */
    protected $cascadeDeletes = [
        'quotes',
    ];

    public const OTHER_ASSET_TYPES = [
        1 => 'HEALTHCARE',
        2 => 'HOSPITALITY',
        3 => 'AGRICULTURE',
        4 => 'NON-PROFITS',
        5 => 'BIFURCATED_ASSETS',
        6 => 'GROUND LEASE',
        7 => 'FEE DEALS',
    ];

    /***************************************************************************************
     ** SCOPES
     ***************************************************************************************/

    public function scopeBetaUser($query)
    {
        return $query->where('beta_user', true);
    }

    public function scopeHasPerfectFit($query)
    {
        return $query->whereNotNull(DB::raw("metas::jsonb->'perfect_fit'"));
    }

    public function scopeByLoanSize($query, int $dealDollarAmount)
    {
        return $query->whereRaw("(users.metas->'perfect_fit'->'loan_size'->>'min')::bigint <= $dealDollarAmount")
                     ->whereRaw("(users.metas->'perfect_fit'->'loan_size'->>'max')::bigint >= $dealDollarAmount");
    }

    public function scopeByDealAssetTypes($query, array $dealAssetTypes)
    {
        return $query->where(function ($query) use ($dealAssetTypes) {
            foreach ($dealAssetTypes as $dealAssetType) {
                $query->orWhereJsonContains('users.metas->perfect_fit->asset_types', $dealAssetType);
            }
        });
    }

    public function scopeByDealLocations($query, array $dealLocations)
    {
        return $query->whereIn('id', function ($query) use ($dealLocations) {
            $query->select('users.id')->from('users')->crossJoin(DB::raw("lateral jsonb_to_recordset(users.metas->'perfect_fit'->'areas') as items(area text)"))
                ->where(function ($query) use ($dealLocations) {
                    foreach ($dealLocations as $dealLocation) {
                        $query->orWhere('items.area', 'ILIKE', '%'.$dealLocation.'%');
                    }
                })
                ->distinct();
        });
    }

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    public function brokers()
    {
        return $this->belongsToMany(Broker::class, 'broker_lender', 'lender_id', 'broker_id')
            ->using(BrokerLender::class);
    }

    /***************************************************************************************
     ** DEAL QUERIES
     ***************************************************************************************/

    public static function getByPreferencesQuery(int $dealDollarAmount, array $dealLocations, array $dealAssetTypes, array $options = []): Builder
    {
        return Lender::hasPerfectFit()
            ->betaUser()
            ->byLoanSize($dealDollarAmount)
            ->byDealLocations($dealLocations)
            ->byDealAssetTypes($dealAssetTypes)
            ->when(count(Arr::get($options, 'excluded_company_ids', [])), function ($query) use ($options) {
                $query->whereNotIn('company_id', $options['excluded_company_ids']);
            })
            ->when(count(Arr::get($options, 'excluded_lender_ids', [])), function ($query) use ($options) {
                $query->whereNotIn('company_id', $options['excluded_lender_ids']);
            });
    }

    /**
     * @param  string  $type
     * @param  Fit  $fit
     * @return bool
     */
    public function updateFit(string $type, Fit $fit): bool
    {
        $metas = $this->metas;
        $metas["${type}_fit"] = $fit;
        $this->metas = $metas;

        return $this->save();
    }

    /**
     * @return null|Fit
     */
    public function getPerfectFit(): ?Fit
    {
        return $this->getFit('perfect');
    }

    /**
     * @return null|Fit
     */
    public function getCloseFit(): ?Fit
    {
        return $this->getFit('close');
    }

    /**
     * @param  string  $type
     * @return null|Fit
     */
    private function getFit(string $type): ?Fit
    {
        $fit = $this->metas["${type}_fit"] ?? [];

        if (empty($fit['areas']) || empty($fit['loan_size'])) {
            return null;
        }

        $sorted = $this->sortFitAttributes($fit);

        return new Fit(...$sorted);
    }

    /**
     * Get Profile info from meta fields
     *
     * @return array
     */
    public function getProfileInfoLender(): array
    {
        $info = $this->metas['profile_info_lender'] ?? [];

        return [
            'biography' => $info['biography'] ?? '',
            'specialty' => $info['specialty'] ?? '',
            'linkedin_url' => $info['linkedin_url'] ?? '',
        ];
    }

    /**
     * @param  array  $fit
     * @return array
     */
    private function sortFitAttributes(array $fit): array
    {
        if (empty($fit['areas']) || empty($fit['loan_size'])) {
            return [];
        }

        $sorted = [$fit['areas'], $fit['loan_size']];

        if (isset($fit['asset_types'])) {
            $sorted[] = $fit['asset_types'];
        }

        if (isset($fit['multifamily'])) {
            $sorted[] = $fit['multifamily'];
        }

        if (! isset($fit['multifamily'])) {
            $sorted[] = null;
        }

        if (isset($fit['other_asset_types'])) {
            $sorted[] = $fit['other_asset_types'];
        }

        if (isset($fit['type_of_loans'])) {
            $sorted[] = $fit['type_of_loans'];
        }

        if (! isset($fit['type_of_loans'])) {
            $sorted[] = [];
        }

        return $sorted;
    }

    /**
     * Send the welcome confirmation email.
     *
     * @param $deal_preferences
     * @return void
     */
    public function sendWelcomeEmailConfirmation($deal_preferences)
    {
        try {
            $this->notify(new WelcomeEmail($deal_preferences));
        } catch (\Throwable $exception) {
            Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($this->email, 'Send welcome email confirmation', $exception));
        }
    }

    /**
     * @param  array  $info_array
     * @return bool
     */
    public function updateProfileInfoLender(array $info_array)
    {
        $metas = $this->metas;
        $metas['profile_info_lender'] = $info_array;
        $this->metas = $metas;

        return $this->save();
    }

    /**
     * @param  Fit  $deal_preferences
     *
     * Check if lender choose suspicious locations
     */
    public function checkSuspiciousLocations(Fit $deal_preferences)
    {
        // Get all lender's areas
        $areas = $deal_preferences->getAreas();
        $isUsa = false;
        $states = [];
        foreach ($areas as $area) {
            // If United states is chosen
            if ($area->area['long_name'] === 'United States') {
                $isUsa = true;
            }
            if ($area->area['state']) {
                $states[] = $area->area['state'];
            }
        }
        // If there is more then 4 states selected
        $isMoreThanFourStates = count(array_unique($states, SORT_STRING)) > 4;

        $name = $this->name();
        $company = $this->getCompanyExport()['company_name'] ?? $this->getOnlyDomain();
        // If suspicious locations send email to admins
        if ($isUsa || $isMoreThanFourStates) {
            $this->sendSuspiciousLocationsAdmin($name, $company);
        }
    }

    /**
     * Send Admin email that lender choose suspicious locations (USA or more than 4 state)
     *
     * @param  string  $name
     * @param  string  $company
     */
    public function sendSuspiciousLocationsAdmin(string $name, string $company)
    {
        $admins = User::query()
            ->where('role', '=', 'admin')
            ->get();
        foreach ($admins as $admin) {
            //Schedule Job
            SuspiciousLocationsJob::dispatch($admin, $name, $company);
        }
    }
}
