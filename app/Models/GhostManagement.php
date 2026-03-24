<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use App\Traits\InterceptsTranslations;
class GhostManagement extends Model
{
    use InterceptsTranslations;
    protected $table = 'ghost_management';
    
    protected $fillable = [
        'tag',
        'title',
        'duration',
        'amount',
        'currency',
        'tag_translation',
        'title_translation',
        'duration_translation',
    ];

    protected $casts = [
        'tag_translation' => 'array',
        'title_translation' => 'array',
        'duration_translation' => 'array',
    ];

    protected $hidden = [
        'tag_translation',
        'title_translation',
        'duration_translation',
    ];
    
}