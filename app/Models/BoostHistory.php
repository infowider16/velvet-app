<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Plan;

class BoostHistory extends Model

{
    protected $table = 'boost_history';

    protected $fillable = [
        'user_id',
        'plan_id',
        'transaction_id',
        'start_date_time',
        'end_date_time',
    ];

  
}
