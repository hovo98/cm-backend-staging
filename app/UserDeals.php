<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDeals extends Model
{
    use HasFactory;
    protected $table = 'user_deal';

    protected $guarded = ['id'];

}
