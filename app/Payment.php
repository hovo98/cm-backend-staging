<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PAID = 'paid';

    protected $guarded = ['id'];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isComplete(): bool
    {
        if ($this->payment_status === self::STATUS_PAID) {
            return true;
        }
        return false;
    }
}
