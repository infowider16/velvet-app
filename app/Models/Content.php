<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InterceptsTranslations;
class Content extends Model
{
    use InterceptsTranslations;
    protected $fillable = [
        'title',
        'slug',
        'description',
        'images',
        'title_translation',
        'slug_translation',
        'description_translation',
    ];

     protected $casts = [
        'description_translation' => 'array',
        'title_translation' => 'array',
        'slug_translation'=> 'array',
         'images' => 'array',
    ];
    protected $hidden = [
        'description_translation',
        'title_translation',
        'slug_translation',
    ];
}