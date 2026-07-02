<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactUs extends Model
{
    protected $table = 'contact_us';

    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'image',
        'user_id',
        'status'
    ];

    /**
     * User Relation
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}