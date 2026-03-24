<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Traits\InterceptsTranslations;

class Boost extends Model
{
    use InterceptsTranslations;
    protected $table = 'boost_management';

    protected $fillable = [
        'tag',
        'title',
        'boost_count',
        'discount',
        'amount',
        'tag_translation',
        'title_translation',
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
