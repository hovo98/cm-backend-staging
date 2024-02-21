<?php

declare(strict_types=1);

namespace App;

use App\DataTransferObjects\Plan;
use App\Exceptions\PaymentException;
use App\Mail\ErrorEmail;
use App\Notifications\ApproveCompanyBroker;
use App\Notifications\ApproveCompanyLender;
use App\Notifications\FinishSecondStep;
use App\Notifications\ResetPassword;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\SuspiciousLocationsAdmin;
use App\Notifications\UpdatePasswordConfirmation;
use App\Notifications\VerifyMailBroker;
use App\Notifications\VerifyMailBroker as VerifyMailBrokerNotification;
use App\Notifications\VerifyMailLender;
use App\Notifications\VerifyMailLender as VerifyMailLenderNotification;
use App\Notifications\WelcomeEmail;
use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Joselfonseca\LighthouseGraphQLPassport\HasLoggedInTokens;
use Laravel\Cashier\Subscription;
use Laravel\Passport\HasApiTokens;
use Spark\Billable;

/**
 * Class User
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class User extends Authenticatable implements MustVerifyEmailInterface
{
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;
    use CanResetPassword;
    use MustVerifyEmail;
    use HasLoggedInTokens;
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role', 'first_name', 'last_name', 'email', 'password', 'phone', 'subscription', 'status', 'profile_image', 'company_id',
        'notify', 'referrer_id', 'timezone', 'beta_user', 'site', 'sent_verify_email_at', 'gtm_hidden_id','chat_response_time',
        'stripe_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'metas' => 'array',
    ];

    /**
     * Column for Soft delete
     */
    public const LENDER_SAVE_DEAL = 1;

    public const LENDER_ARCHIVE_DEAL = 2;

    public const LENDER_IGNORE_DEAL = 3;

    public const LENDER_DEAL_PUBLISHED = 4;

    public const LIVE_SIGNUP = 1;

    public const BETA_SIGNUP = 2;

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    /**
     * @return BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lenderRooms()
    {
        return $this->hasMany(Room::class, 'lender_id');
    }

    public function brokerRooms()
    {
        return $this->hasMany(Room::class, 'lender_id');
    }

    public function relatedDeals()
    {
        return $this->hasMany(UserDeals::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id')->orderBy('created_at', 'desc');
    }

    public function activeSubscription()
    {
        return $this->subscriptions()->active()->latest()->first();
    }

    /**
     * @param  string  $email
     * @return string|null
     */
    public static function userExistsByEmail(string $email): ?string
    {
        $user = DB::table('users')
            ->select(['id', 'role'])
            ->where('email', '=', $email)
            ->first();

        return $user->role ?? null;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $notification = new ResetPasswordNotification($token);
        $notification->onQueue('emails');
        $notification::createUrlUsing('App\Notifications\ResetPassword::generateResetUrl');

        $this->notify($notification);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        //Check if this first time sending email for lender
        $sentVerifyEmailAt = $this->sent_verify_email_at ? true : false;
        $this->update(['sent_verify_email_at' => now()]);
        if ($this->role === 'lender') {
            $this->notify(new VerifyMailLenderNotification($sentVerifyEmailAt));
        } elseif ($this->role === 'broker') {
            try {
                $this->notify(new VerifyMailBrokerNotification());
            } catch (\Throwable $exception) {
                Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($this->email, 'Send the email verification notification', $exception));
            }
        }
    }

    public function isBroker(): bool
    {
        return $this->role === 'broker';
    }

    public function isLender(): bool
    {
        return $this->role === 'lender';
    }

    /**
     * @param string $customerId
     * @return mixed
     */
    public static function findByStripeId(string $customerId)
    {
        return static::where('stripe_id', $customerId)->first();
    }

    public function activePlan()
    {
        return $this->subscriptions()->active()->latest()->first();
    }

    /**
     * User's full name
     *
     * @return string
     */
    public function name(): string
    {
        return implode(' ', [$this->first_name, $this->last_name]);
    }

    /**
     * Wrapper for User::name()
     *
     * Serves to add support for some Laravel plugins which require 'name' attribute to be set on the model
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->name();
    }

    /**
     * Get user's domain
     *
     * @return string
     */
    public function getDomainAttribute(): string
    {
        return explode('@', $this->email)[1];
    }

    /**
     * Get only domain
     *
     * @return string
     */
    public function getOnlyDomain(): string
    {
        return ucfirst(substr($this->getDomainAttribute(), 0, strpos($this->getDomainAttribute(), '.')));
    }

    public function activeTokens()
    {
        return $this->tokens()->where('revoked', false);
    }

    /**
     * Calculates the gravatar image url for the user
     *
     * @param  int  $size
     * @return string
     */
    public function gravatar(int $size = 80): string
    {
        $hash = md5(strtolower(trim($this->email)));

        return "https://www.gravatar.com/avatar/${hash}.jpg?d=mp&s=${size}";
    }

    /** @TODO  what happens when deal has no dollar amount */
    /**
     * @param Deal $deal
     * @return bool
     * @throws PaymentException
     */
    public function canAccept(Deal $deal): bool
    {
        if ($deal->isPremium()) {
            return true;
        }

        if (!$this->subscribed()) {
            throw new PaymentException("subscription_required");
        }

        $loanType = data_get($deal->data, 'loan_type');

        if ($loanType === 1) {
            $dollarAmount = data_get($deal->data, 'purchase_loan.loan_amount');
        }
        if ($loanType === 2) {
            $dollarAmount = data_get($deal->data, 'refinance_loan.loanAmount');
        }
        if ($loanType === 3) {
            $dollarAmount = data_get($deal->data, 'construction_loan.loanAmount');
        }

        return (new Plan())
            ->from($this->activePlan()->stripe_price)
            ->isValidAmount($dollarAmount);
    }

    /**
     * Get company for User
     *
     * @return array
     */
    public function getCompany(): array
    {
        // Main data from Company Entity
        $company = $this->company()->first();

        if (! $company) {
            return [];
        }

        $company_data = $this->metas['company_data'] ?? [];

        $company_data['company_logo'] = $company_data['company_logo'] ?? '';

        if (config('filesystems.default') === 's3') {
            if (! empty($company_data['company_logo']) && Storage::disk(config('app.app_image_upload'))->exists($company_data['company_logo'])) {
                $company_data['company_logo'] = Storage::disk(config('app.app_image_upload'))->url($company_data['company_logo']);
            }
        } else {
            $company_data['company_logo'] = $company_data['company_logo'] ? asset($company_data['company_logo']) : '';
        }

        // Get user overrides from meta fields
        $overrides = [
            'id' => $company->id,
            'domain' => $company->domain,
            'company_name' => $company_data['company_name'] ?? '',
            'company_address' => $company_data['company_address'] ?? '',
            'company_city' => $company_data['company_city'] ?? '',
            'company_state' => $company_data['company_state'] ?? '',
            'company_zip_code' => $company_data['company_zip_code'] ?? '',
            'company_phone' => $company_data['company_phone'] ?? '',
            'company_logo' => $company_data['company_logo'],
            'company_status' => $company->getCompanyStatus(),
        ];

        return $overrides;
    }

    public function getCompanyNameFromMetasOrFromCompanyRelationship()
    {
        $companyNameFromMeta = $this->metas['company_data']['company_name'] ?? null;
        if (!empty($companyNameFromMeta)) {
            return $companyNameFromMeta;
        }

        $company = $this->company;

        return $company?->company_name ?: $this->domainNameFromDomain($company?->domain);
    }

    private function domainNameFromDomain($domain)
    {
        if (!$domain) {
            return null;
        }

        return ucfirst(substr($domain, 0, strpos($domain, '.')));
    }

    /**
     * @return array|Model|BelongsTo|object
     */
    public function getCompanyExport()
    {
        // Main data from Company Entity
        $company = $this->company()->first();

        if (! $company) {
            return [];
        }

        return $company;
    }

    /**
     * @param  array  $companyUpdateData
     * @return bool
     */
    public function updateCompanyData(array $companyUpdateData)
    {
        $metas = $this->metas ?? [];

        if (array_key_exists('company_data', $metas)) {
            $metas['company_data'] = array_merge($metas['company_data'], $companyUpdateData);
        } else {
            $metas['company_data'] = $companyUpdateData;
        }
        $this->metas = $metas;

        return $this->save();

        // Log::debug($metas);
        // $company = $this->company()->first();
        // $company->update($metas['company_data']);

        // return $this->update([
        //     'company_name' => $this->metas["company_data"]['company_name'],
        //     'domain' => $this->metas["company_data"]['domain'],
        //     'company_address' => $this->metas["company_data"]['company_address'],
        //     'company_city' => $this->metas["company_data"]['company_city'],
        //     'company_state' =>$this->metas["company_data"]['company_state'],
        //     'company_zip_code' => $this->metas["company_data"]['company_zip_code'],
        //     'company_phone' => $this->metas["company_data"]['company_phone'],
        //     // 'company_status' => $company_status
        // ]);
    }

    /**
     * Send the update password confirmation email.
     *
     * @return void
     */
    public function sendUpdatePasswordConfirmation()
    {
        try {
            $this->notify(new UpdatePasswordConfirmation());
        } catch (\Throwable $exception) {
            Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($this->email, 'Send the update password confirmation email', $exception));
        }
    }

    /**
     * Get the user's profile image.
     *
     * @param  string  $value
     * @return string
     */
    public function getProfileImageAttribute($value): string
    {
        if (! $value) {
            return '';
        }

        if (config('filesystems.default') === 's3') {
            if (! Storage::disk(config('app.app_image_upload'))->exists($value)) {
                return '';
            }

            return Storage::disk(config('app.app_image_upload'))->url($value);
        }

        return $value ? asset($value) : '';
    }

    /**
     * @param $deal_id
     * @param $relation_type
     */
    public function storeRelationUserDeal($deal_id, $relation_type)
    {
        $userDealRelation = new UserDeals();
        $userDealRelation->user_id = $this->id;
        $userDealRelation->deal_id = $deal_id;
        $userDealRelation->relation_type = $relation_type;
        $userDealRelation->save();
    }

    /**
     * @param $deal_id
     * @param $relation_type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function checkRelatedDeal($deal_id, $relation_type)
    {
        return $this->relatedDeals()->where('relation_type', $relation_type)->where('user_id', $this->id)->where('deal_id', $deal_id)->get();
    }

    /**
     * @param $deal_id
     * @param $relation_type
     */
    public function removeRelation($deal_id, $relation_type)
    {
        $this->relatedDeals()->where('relation_type', $relation_type)->where('user_id', $this->id)->where('deal_id', $deal_id)->delete();
    }

    /**
     * @param $manageTable
     * @param $table
     * @return bool
     *
     * Update values for manage tables deals and quotes
     */
    public function updateManageTable($manageTable, $table)
    {
        $metas = $this->metas ?? [];
        $metas[$table.'ManageTable'] = $manageTable;

        $this->metas = $metas;
        $this->save();

        return $this->metas[$table.'ManageTable'];
    }

    /**
     * @param $table
     * @return array
     *
     * Get values for manage tables deals and quotes
     */
    public function getManageTable($table)
    {
        return $this->metas[$table.'ManageTable'] ?? [];
    }

    /**
     * @param $email
     * @return bool
     */
    public static function allowedUsersBeta($email): bool
    {
        $env = config('app.env');
        $user = User::where('email', trim($email))->withTrashed()->first();

        if (! $user) {
            return false;
        }

        if (config('app.skip_company_approval') || in_array($env, ['testing'])) {
            return true;
        }

        // // Is this user approved?
        return $user->beta_user;
    }

    /**
     * @param $notification
     * @return bool
     */
    public function checkNotification($notification): bool
    {
        if ($notification instanceof VerifyMailLender || $notification instanceof VerifyMailBroker
            || $notification instanceof ApproveCompanyLender || $notification instanceof ApproveCompanyBroker
            || $notification instanceof WelcomeEmail || $notification instanceof FinishSecondStep
            || $notification instanceof SuspiciousLocationsAdmin
            || $notification instanceof ResetPassword || $notification instanceof UpdatePasswordConfirmation) {
            return false;
        }

        return true;
    }

    /**
     *  Check if verify email is already sent in last hour
     *
     * @return bool
     */
    public function checkShouldVerifyEmailBeSent(): bool
    {
        $timeSent = $this->sent_verify_email_at;
        if (! $timeSent) {
            return true;
        }

        return $timeSent < Carbon::now()->subMinutes(10);
    }

    public function hasPurchasedDeal(Deal $deal): bool
    {
        return Payment::where('deal_id', $deal->id)
            ->where('user_id', $this->id)
            ->where('payment_status', 'paid')
            ->exists();
    }

    /**
     * @return BelongsTo
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
}
