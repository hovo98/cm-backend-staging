<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Company;

/**
 * Class CompanyEventSubscriber
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CompanyEventSubscriber
{
    /**
     * Send email when user is registered
     *
     * @param $event
     */
    public function sendApprovalMail($event)
    {
        /** @var Company $company */
        $company = $event->model();

        if ($event->event() === 'createdByUser') {
            // Call method in Company
            $company->sendCompanyApproval();
        }
    }

    /**
     * Send users that domain is approved
     *
     * @param $event
     */
    public function sendApprovedByAdminMail($event)
    {
        /** @var Company $company */
        $company = $event->model();

        if ($event->event() === 'approvedByAdmin') {
            // Call method in Company
            $company->sendApprovedByAdminMail();
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\CompanyChanged::class,
            'App\Listeners\CompanyEventSubscriber@sendApprovalMail'
        );

        $events->listen(
            \App\Events\CompanyChanged::class,
            'App\Listeners\CompanyEventSubscriber@sendApprovedByAdminMail'
        );
    }
}
