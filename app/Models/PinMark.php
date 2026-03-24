<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PinMark extends Model
{
    use HasFactory;

    protected $table = 'pin_marks';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
            'user_id',
            'country_code',
            'pin_message',
            'commented_on',
            'status',
            'total_like',
        ];

    /**
     * Casts
     */
    protected $casts = [
        'commented_on' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Relationship: Comment belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Filter records within last N hours based on Swiss time
     *
     * @param Builder $query
     * @param string|Carbon|null $swissTime
     * @param int $hours
     * @param string $column
     */
    public function scopeWithinHoursSwiss(
        Builder $query,
        string|Carbon|null $swissTime,
        int $hours = 48,
        string $column = 'created_at'
    ): Builder {

        // ✅ Use server / DB time only (UTC)
        $now = Carbon::now();
    
        $from = $now->copy()->subHours($hours);
        $to   = $now->copy();
    
        return $query->whereBetween($column, [$from, $to]);
    }
}
