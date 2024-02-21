<?php

declare(strict_types=1);

namespace App\GraphQL\Builders;

use App\Lender;

/***
 * Class Lenders
 * @package App\GraphQL\Builders
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class Lenders
{
    //Returns only Users where role is lender
    public function __invoke()
    {
        return Lender::query()
            ->where('role', '=', 'lender');
    }
}
