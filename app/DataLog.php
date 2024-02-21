<?php

namespace App;

// extends
use Illuminate\Database\Eloquent\Model;

// includes

class DataLog extends Model
{
    protected $table = 'data_logs';

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'data' => 'array',
    ];

    public $timestamps = true;

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    public function dataLoggable()
    {
        return $this->morphTo(__FUNCTION__, 'data_loggable_type', 'data_loggable_id');
    }

    /***************************************************************************************
     ** CREATE / UPDATE
     ***************************************************************************************/

    public static function recordSimple(string $type, string $slug, array $data = null, $description = null)
    {
        $log = new self();
        $log->type = $type;
        $log->slug = $slug;
        $log->data = $data;
        $log->description = $description;
        $log->save();

        return $log;
    }

    public static function recordForModel(Model $model, string $type, string $slug, array $data = null, $description = null)
    {
        $log = new self();
        $log->dataLoggable()->associate($model);
        $log->type = $type;
        $log->slug = $slug;
        $log->data = $data;
        $log->description = $description;
        $log->save();

        return $log;
    }
}
