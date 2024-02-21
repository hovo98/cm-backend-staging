<?php

declare(strict_types=1);

namespace App;

use App\Mail\ErrorEmail;
use App\Notifications\ApproveCompanyBroker as ApproveCompanyBrokerNotification;
use App\Notifications\ApproveCompanyLender as ApproveCompanyLenderNotification;
use App\Notifications\ApprovedDomainBrokers as ApproveDomainBrokersNotification;
use App\Notifications\ApprovedDomainLenders as ApprovedDomainLendersNotification;
use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

/**
 * Class Company
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Company extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_name', 'domain', 'company_address', 'company_city', 'company_state', 'company_zip_code', 'company_phone', 'is_approved', 'company_logo', 'company_status',
    ];

    /**
     * Column for Soft delete
     */
    public const APPROVED_COMPANY_STATUS = 1;

    public const PENDING_COMPANY_STATUS = 2;

    public const DECLINE_COMPANY_STATUS = 3;

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function emails()
    {
        return $this->hasMany(EmailNotification::class, 'company_id');
    }

    /**
     * Send Admin email that new Company is created and needs approval
     */
    public function sendCompanyApproval()
    {
        $admins = User::query()
            ->where('role', '=', 'admin')
            ->get();

        //Check role to send different emails
        $userRole = User::where('company_id', $this->id)->first();

        foreach ($admins as $admin) {
            if ($userRole->role === 'lender') {
                try {
                    $admin->notify(new ApproveCompanyLenderNotification($this->id, $this->domain));
                } catch (\Throwable $exception) {
                    Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($admin->email, 'Send Admin email that new Company is created and needs approval', $exception));
                }
            } else {
                try {
                    $admin->notify(new ApproveCompanyBrokerNotification($this->id, $this->domain));
                } catch (\Throwable $exception) {
                    Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($admin->email, 'Send Admin email that new Company is created and needs approval', $exception));
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getCompanyStatus(): string
    {
        if ($this->company_status === Company::APPROVED_COMPANY_STATUS) {
            return 'Approved';
        } elseif ($this->company_status === Company::DECLINE_COMPANY_STATUS) {
            return 'Decline';
        } else {
            return 'Pending';
        }
    }

    /**
     * @return array[]
     */
    public function getCompanyStatusInt(): array
    {
        return [
            [
                'value' => Company::APPROVED_COMPANY_STATUS,
                'status' => 'Approved',
            ],
            [
                'value' => Company::DECLINE_COMPANY_STATUS,
                'status' => 'Decline',
            ],
            [
                'value' => Company::PENDING_COMPANY_STATUS,
                'status' => 'Pending',
            ],
        ];
    }

    public function sendApprovedByAdminMail()
    {
        $approvedUsers = User::query()
            ->where('company_id', '=', $this->id)
            ->get();

        // Check if there is approved users
        if ($approvedUsers->isEmpty()) {
            return;
        }

        foreach ($approvedUsers as $approvedUser) {
            if ($approvedUser->role === 'broker') {
                try {
                    $approvedUser->notify(new ApproveDomainBrokersNotification($this->domain));
                } catch (\Throwable $exception) {
                    Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($approvedUser->email, 'Send user that company is approval', $exception));
                }
            } elseif ($approvedUser->role === 'lender') {
                try {
                    $approvedUser->notify(new ApprovedDomainLendersNotification($this->domain));
                } catch (\Throwable $exception) {
                    Mail::mailer(config('mail.alternative_mailer'))->send(new ErrorEmail($approvedUser->email, 'Send user that company is approval', $exception));
                }
            }
        }
    }
}
