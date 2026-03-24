<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinMarkLike extends Model
{
    protected $table = 'pin_mark_likes';

    protected $fillable = [
        'pin_mark_id',
        'user_id',
    ];
}
