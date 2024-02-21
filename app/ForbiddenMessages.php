<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForbiddenMessages extends Model
{
    use HasFactory;

    protected $fillable = [
        'message', 'quote_id', 'user_id',
    ];
}
