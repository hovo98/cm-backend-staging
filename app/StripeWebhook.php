<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeWebhook extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array'
    ];

    /**
     * @return self
     */
    public static function log($event): self
    {
        return static::create(['payload' => $event->payload]);
    }

    public function setProcessed(): void
    {
        $this->update([
            'processed_at' => now(),
        ]);
    }
}
