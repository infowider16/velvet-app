<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\InterceptsTranslations;

class Interest extends Model
{
    use InterceptsTranslations;

    protected $fillable = [
        'parent_id',
        'name',
        'name_translation',
    ];

    protected $casts = [
        'name_translation' => 'array',
    ];

    protected $hidden = [
        'name_translation',
    ];

    public function parent()
    {
        return $this->belongsTo(Interest::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Interest::class, 'parent_id');
    }
}