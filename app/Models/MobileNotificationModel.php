<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\InterceptsTranslations;

class MobileNotificationModel extends Model
{
    use InterceptsTranslations;
    use HasFactory;
    
    protected $table = 'mobile_notifications';
    protected $guarded = [];
    public $timestamps = true;

    protected $casts = [
        'body_translation' => 'array',
        'title_translation' => 'array',
    ];

    protected $hidden = [
        'body_translation',
        'title_translation',
    ];
}
