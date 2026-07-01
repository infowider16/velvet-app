<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupReport extends Model
{
    //
    use HasFactory;
    protected $guarded = [];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function pinmark()
    {
        return $this->belongsTo(PinMark::class, 'pin');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
