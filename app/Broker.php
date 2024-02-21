<?php

declare(strict_types=1);

namespace App;

use App\Services\QueryServices\Lender\Brokers\CheckConnectedLendersDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Broker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Broker extends User
{
    use HasFactory;

    /**
     * Broker is saved in users table
     */
    protected $table = 'users';

    protected $attributes = [
        'role' => 'broker',
    ];

    /**
     * Relationships for Soft delete on cascade
     */
    protected $cascadeDeletes = ['deals', 'lenderEmails'];

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    /**
     * @return HasMany
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'user_id');
    }

    /**
     * @return BelongsToMany
     */
    public function lenders()
    {
        return $this->belongsToMany(Lender::class, 'broker_lender', 'broker_id', 'lender_id')
                    ->using(BrokerLender::class);
    }

    /**
     * @return BelongsToMany
     */
    public function lenderEmails()
    {
        return $this->belongsToMany(Broker::class, 'broker_lender_email', 'broker_id')
                    ->using(BrokerLenderEmail::class)
                    ->withPivot(['email as lender_email', 'deleted_at']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     *
     * On Broker model get received quotes for all his deals
     */
    public function quotes()
    {
        return $this->hasManyThrough(\App\Quote::class, \App\Deal::class, 'user_id')->where('quotes.finished', true);
    }

    /**
     * @param $broker_id
     * @param $emailDomainOnly
     * @return bool
     */
    public function checkInvitationTable($broker_id, $emailDomainOnly)
    {
        // Check domain of Lender in broker email table
        $broker = User::find($broker_id);

        return $broker->lenderEmails()->where('broker_lender_email.email', 'LIKE', '%@'.$emailDomainOnly.'%')->first() ?? false;
    }

    /**
     * @param $broker_id
     * @param $emailDomainOnly
     * @return bool
     */
    public function checkPivotTable($broker_id, $emailDomainOnly)
    {
        //Return all ids of brokers that are connected to colleagues
        $connectedLendersDomain = resolve(CheckConnectedLendersDomain::class);
        $checkConnectedLendersDomain = $connectedLendersDomain->run(['domain' => $emailDomainOnly]);
        if ($checkConnectedLendersDomain->isEmpty()) {
            return false;
        }

        return $checkConnectedLendersDomain->contains($broker_id);
    }
}
