<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\UserChanged;
use App\Traits\ModelObserver;
use App\User;

/**
 * Class UserObserver
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class UserObserver
{
    use ModelObserver;

    /**
     * Handle the user "created" event.
     *
     * @param  User  $user
     * @return void
     */
    public function created(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'created');
    }

    /**
     * Handle the lender "created" during sign up process event for welcome email.
     *
     * @param  User  $user
     * @return void
     */
    public function createdLender(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'createdLender');
    }

    /**
     * Handle the user "updated" event.
     *
     * @param  User  $user
     * @return void
     */
    public function updated(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'updated');
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'deleted');
    }

    /**
     * Handle the user "restored" event.
     *
     * @param  User  $user
     * @return void
     */
    public function restored(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'restored');
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param  User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        $this->fireModelEvent($user, UserChanged::class, 'forceDeleted');
    }
}
