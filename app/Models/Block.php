<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
    ];

    // User who blocked
    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    // User who was blocked
    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}
