<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;

/**
 * Class UnsaveUnarchive
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UnsaveUnarchive extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['lender'];
        $flag = true;
        $type = $args['type'];

        foreach ($args['deals'] as $dealInput) {
            //Check if this deal is already archived or saved deal
            $archivedDeals = $user->checkRelatedDeal($dealInput['id'], $type);

            //If the deal is not archived or saved return
            if ($archivedDeals->isEmpty()) {
                $flag = false;

                continue;
            }

            // Remove relation
            $user->removeRelation($dealInput['id'], $type);
        }

        return [
            'status' => $flag,
        ];
    }
}
