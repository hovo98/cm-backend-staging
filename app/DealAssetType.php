<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class DealAssetType
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DealAssetType extends Pivot
{
    use HasFactory;

    protected $table = 'deal_asset_type';
}
