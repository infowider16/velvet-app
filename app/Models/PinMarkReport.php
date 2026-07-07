<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinMarkReport extends Model
{
    use HasFactory;

    protected $table = 'group_reports';

    protected $fillable = [
        'pin',
        'user_id',
        'report_type',
        'reason',
        'status',
    ];

    /**
     * Relationship: Report belongs to PinMark
     */
    public function pinMark()
    {
        return $this->belongsTo(PinMark::class, 'pin');
    }

    /**
     * Relationship: Report belongs to User (reporter)
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
