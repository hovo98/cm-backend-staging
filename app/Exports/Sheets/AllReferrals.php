<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Lender;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AllReferrals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllReferrals extends SheetAbstract implements SheetInterface
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return User::query()->select(['referrer_id as id'])->distinct('referrer_id')->whereNotNull('referrer_id');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Referrals';
    }

    /**
     * @param  Model  $obj
     * @return array
     */
    public function map(Model $obj): array
    {
        $allData = collect();

        $obj = $obj->fresh();

        if ($this->getMappedReferrals($obj)) {
            $allData->add($obj->name().','.$obj->email);
            $allData->add($this->getMappedReferrals($obj));
        }

        return $allData
            ->map([$this, 'mapNullToString'])
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function headings(): Collection
    {
        return collect([
            'User who invited this user',
            'User who signed up with a link',
        ]);
    }

    /**
     * @param $lender
     * @return string
     */
    private function getMappedReferrals($lender): string
    {
        $referrals = '';
        $users = Lender::where('role', 'lender')->where('referrer_id', $lender->id)->get();
        if ($users->isNotEmpty()) {
            $referral = [];
            foreach ($users as $user) {
                $referral[] = $user->name().', '.$user->email;
            }
            $referrals = implode(';', $referral);
        }

        return $referrals;
    }
}
