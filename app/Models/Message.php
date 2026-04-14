<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    //
    use HasFactory;
    protected $guarded = [];


    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function getIsTextAttribute()
    {
        return $this->media_type === null && !empty($this->message_text);
    }
    // Check if message is media (image/video/audio/file)
    public function getIsMediaAttribute()
    {
        return !empty($this->media_type);
    }

    // Full media URL accessor
    public function getMediaFullUrlAttribute()
    {
        return $this->media_url ? asset('storage/' . $this->media_url) : null;
    }

    // Thumb full path
    public function getThumbnailFullUrlAttribute()
    {
        return $this->thumbnail ? asset('storage/' . $this->thumbnail) : null;
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
   
}
