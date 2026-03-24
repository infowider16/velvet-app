<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinMarkComment extends Model
{
    use HasFactory;

    protected $table = 'pin_mark_comments';

    protected $fillable = [
        'pin_mark_id',
        'user_id',
        'comment',
        'commented_on',
        'total_like',
        'status',
    ];

    protected $casts = [
        'commented_on' => 'datetime',
        'status' => 'integer',
    ];

    protected $attributes = [
        'status' => 1,
        'total_like' => 0,
    ];

    public function pinMark()
    {
        return $this->belongsTo(PinMark::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
