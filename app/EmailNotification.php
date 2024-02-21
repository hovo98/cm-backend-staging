<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    protected $table = 'email_notifications';

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'params' => 'array',
        'failed_at' => 'datetime:Y-m-d H:i:s',
    ];

    /***************************************************************************************
     ** RELATIONS
     ***************************************************************************************/

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function referenceable()
    {
        return $this->morphTo();
    }

    /***************************************************************************************
     ** GENERAL
     ***************************************************************************************/

    public static function makeOne(User $sentBy = null, User $recipient = null, Model $reference = null, string $mailable, array $params, string $email = null)
    {
        $mail = new EmailNotification();
        $mail->company_id = $recipient?->company->id;
        $mail->sender_id = $sentBy?->id;
        $mail->recipient_id = $recipient?->id;
        if ($reference) {
            $mail->referenceable()->associate($reference);
        }
        $mail->mailable = $mailable;
        $mail->params = $params;
        $mail->recipient_email = $recipient ? $recipient->email : $email;
        $mail->save();

        return $mail;
    }

    public function setFailed(string $failedReason = null)
    {
        $this->failed_reason = $failedReason;
        $this->failed_at = now();
        $this->save();
    }
}
