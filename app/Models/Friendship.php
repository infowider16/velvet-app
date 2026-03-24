<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    // Requester (who sent the request)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    // Receiver (who got the request)
    public function friend()
    {
        return $this->belongsTo(User::class, 'friend_id','id');
    }
}
