<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    //
    use HasFactory;
    protected $guarded = [];

    public function members()
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function reports()
    {
        return $this->hasMany(GroupReport::class, 'group_id');
    }

    public function media()
    {
        return $this->hasMany(Message::class, 'group_id', 'id')
            ->with('sender')
            ->where(function ($query) {
                $query->whereNotNull('media_url')
                    ->orWhereNotNull('document_url')
                    ->orWhereNotNull('link_url');
            })
            ->latest();
    }

}
