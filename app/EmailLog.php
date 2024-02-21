<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $table = 'email_logs';
    protected $guarded = ['id'];
    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];


    /***************************************************************************************
     ** SCOPES
     ***************************************************************************************/

    public function scopeByUuid($query, string $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public static function makeOne(string $uuid, array $recipients, ?string $subject, ?string $message)
    {
        $emailLog = new EmailLog();
        $emailLog->uuid = $uuid;
        $emailLog->status = 'sending';
        $emailLog->subject = $subject;
        $emailLog->recipients = collect($recipients)->map(fn ($recipient) => $recipient->getAddress())->implode(', ');
        $emailLog->message = $message;
        $emailLog->save();

        return $emailLog;
    }

    public function markSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }
}
