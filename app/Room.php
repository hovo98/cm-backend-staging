<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['company'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function broker()
    {
        return $this->belongsTo(User::class, 'broker_id')->withTrashed();
    }

    public function lender()
    {
        return $this->belongsTo(User::class, 'lender_id')->withTrashed();
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class, 'deal_id')->withTrashed();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /***************************************************************************************
     ** SCOPES
     ***************************************************************************************/

    public function scopeByLender($query, User $user)
    {
        return $query->where('lender_id', $user->id);
    }

    public function lastMessage()
    {
        $msg = Message::select()
        ->where('room_id', $this->id)
        ->orderBy('created_at', 'desc')->first();

        if (! $msg) {
            return '';
        }

        return $msg['message'];
    }

    public function lastMessageTime()
    {
        $msg = Message::select()
        ->where('room_id', $this->id)
        ->orderBy('created_at', 'desc')->first();
        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $msg->created_at);
        $date->setTimezone('America/New_York');

        return $date->format('m/d/Y H:i A');
    }
}
