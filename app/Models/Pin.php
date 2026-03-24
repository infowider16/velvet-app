<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InterceptsTranslations;

class Pin extends Model
{
    use InterceptsTranslations;

    protected $table = 'pin_management';

    protected $fillable = [
        'tag',
        'tag_translation',
        'title',
        'title_translation',
        'pin_count',
        'discount',
        'amount',
    ];

    protected $casts = [
        'tag_translation' => 'array',
        'title_translation' => 'array',
    ];

    protected $hidden = [
        'tag_translation',
        'title_translation',
    ];
}