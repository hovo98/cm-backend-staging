<?php

declare(strict_types=1);

namespace App;

use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Config
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Config extends Model
{
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;

    /**
     * Config is saved in config table
     */
    protected $table = 'config';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value',
    ];

    /**
     * Column for Soft delete
     */
    public function getValueAttribute($value)
    {
        if (is_serialized($value)) {
            return unserialize($value);
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? serialize($value) : $value;

        return $this;
    }
}
