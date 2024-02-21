<?php

declare(strict_types=1);

namespace App\Observers;

use App\Company;
use App\Events\CompanyChanged;
use App\Traits\ModelObserver;

/**
 * Class CompanyObserver
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CompanyObserver
{
    use ModelObserver;

    /**
     * Handle the company "created" event.
     *
     * @param  Company  $company
     * @return void
     */
    public function created(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'created');
    }

    /**
     * Handle the company "created" during sign up process event.
     *
     * @param  Company  $company
     * @return void
     */
    public function createdByUser(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'createdByUser');
    }

    /**
     * Handle when the company is approved by admin in the dashboard event.
     *
     * @param  Company  $company
     * @return void
     */
    public function approvedByAdmin(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'approvedByAdmin');
    }

    /**
     * Handle the company "updated" event.
     *
     * @param  Company  $company
     * @return void
     */
    public function updated(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'updated');
    }

    /**
     * Handle the company "deleted" event.
     *
     * @param  Company  $company
     * @return void
     */
    public function deleted(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'deleted');
    }

    /**
     * Handle the company "restored" event.
     *
     * @param  Company  $company
     * @return void
     */
    public function restored(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'restored');
    }

    /**
     * Handle the company "force deleted" event.
     *
     * @param  Company  $company
     * @return void
     */
    public function forceDeleted(Company $company)
    {
        $this->fireModelEvent($company, CompanyChanged::class, 'forceDeleted');
    }
}
