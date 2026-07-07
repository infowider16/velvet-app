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

    public function pinMark()
    {
        return $this->belongsTo(PinMark::class, 'pin_mark_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
