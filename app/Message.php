<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'seen'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id')->withTrashed();
    }

    public function getFormattedDateAttribute()
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);
        $date->setTimezone('America/New_York');

        return $date->format('m/d/Y H:i A');
    }
}
