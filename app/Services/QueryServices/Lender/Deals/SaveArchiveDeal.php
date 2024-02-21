<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;

/**
 * Class SaveArchiveDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SaveArchiveDeal extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['lender'];
        $flag = true;
        $countDeals = count($args['deals']);
        $countExisting = 0;
        if ($args['msg'] === 'saved') {
            $args['msg'] = 'added to Favorites';
        }
        $message = $countDeals === 1 ? 'Deal has been '.$args['msg'].'.' : 'Deals have been '.$args['msg'].'.';

        foreach ($args['deals'] as $dealInput) {
            //Check if this deal is already archived or saved deal
            $relatedTypeDeals = $user->checkRelatedDeal($dealInput['id'], $args['type']);
            $checkRelatedTypeDeals = $user->checkRelatedDeal($dealInput['id'], $args['checkType']);

            // If deal is already archived or saved return
            if ($relatedTypeDeals->isNotEmpty()) {
                $countExisting++;
            }

            // If deal is already saved or archived remove relation
            if ($checkRelatedTypeDeals->isNotEmpty()) {
                $user->removeRelation($dealInput['id'], $args['checkType']);
            }

            // Store new relation
            $user->storeRelationUserDeal($dealInput['id'], $args['type']);
        }

        if ($countDeals === $countExisting) {
            $flag = false;
            if ($args['msg'] === 'saved') {
                $args['msg'] = 'in Favorites';
            }
            $message = $countDeals === 1 ? 'Looks like this deal is already '.$args['msg'] : 'Looks like some of these deals are already '.$args['msg'];
        }

        return [
            'status' => $flag,
            'message' => $message,
        ];
    }
}
