<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Broker;
use App\BrokerLender;
use App\BrokerLenderEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AllConnections
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllConnections extends SheetAbstract implements SheetInterface
{
    public function query(): Builder
    {
        $haveConnections = BrokerLenderEmail::query()
            ->select('broker_id as id')
            ->distinct('broker_id')
            ->get()
            ->pluck('id');

        $haveInvited = BrokerLender::query()
            ->select('broker_id as id')
            ->distinct('broker_id')
            ->get()
            ->pluck('id');

        $in = $haveConnections->merge($haveInvited)->toArray();

        return Broker::query()->whereIn('id', $in);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Broker Lender connections';
    }

    /**
     * @param  Broker  $obj
     * @return array
     */
    public function map(Model $obj): array
    {
        $connections = $this->mappedLenders($obj->lenders()->get());
        $invitedLenders = $this->mappedInvitedLenders($obj->lenderEmails()->select('broker_lender_email.email')->get());
        if ($connections || $invitedLenders) {
            return collect([
                $obj->name(), $obj->email, $connections, $invitedLenders,
            ])
                ->map([$this, 'mapNullToString'])
                ->toArray();
        }

        return [];
    }

    /**
     * @return Collection
     */
    public function headings(): Collection
    {
        return collect([
            'Broker name', 'Broker email', 'Connected lenders', 'Invited lenders',
        ]);
    }

    /**
     * @param $connections
     * @return string
     */
    private function mappedLenders($connections)
    {
        $lenders = '';
        if ($connections->isEmpty()) {
            return $lenders;
        }
        foreach ($connections as $connection) {
            $lenders .= $connection->name().','.$connection->email.'; ';
        }

        return $lenders;
    }

    /**
     * @param $invited
     * @return string
     */
    private function mappedInvitedLenders($invited)
    {
        $invitedLenders = '';
        if ($invited->isEmpty()) {
            return $invitedLenders;
        }
        foreach ($invited as $invite) {
            $invitedLenders .= $invite->email.'; ';
        }

        return $invitedLenders;
    }
}
