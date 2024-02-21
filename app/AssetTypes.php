<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class AssetTypes
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AssetTypes extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    /**
     * @return BelongsToMany
     */
    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_asset_type', 'asset_type_id', 'deal_id')
                    ->using(DealAssetType::class);
    }
}
