<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutedFriend extends Model
{
    protected $table = 'muted_friends';

    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // The user who muted someone
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // The friend who is muted
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Optional but recommended)
    |--------------------------------------------------------------------------
    */

    // ✅ Fast scope (READ-ONLY)
    public function scopeIsMuted($query, int $userId, int $friendId)
    {
        return $query->where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->where('status', 1);
    }

    // ✅ Fast boolean check (no scope overhead if you prefer)
    public static function isMutedCheck(int $userId, int $friendId): bool
    {
        return self::where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->where('status', 1)
            ->exists();
    }

    // ✅ Mute (write)
    public static function mute(int $userId, int $friendId): void
    {
        self::updateOrCreate(
            ['user_id' => $userId, 'friend_id' => $friendId],
            ['status' => 1]
        );
    }

    // ✅ Unmute (write)
    public static function unmute(int $userId, int $friendId): void
    {
        self::updateOrCreate(
            ['user_id' => $userId, 'friend_id' => $friendId],
            ['status' => 0]
        );
    }
    
    public static function checkOrCreate(int $userId, int $friendId): bool
    {
        $record = self::where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->first();
    
        // If record does not exist → create with status = 1
        if (!$record) {
            self::create([
                'user_id'   => $userId,
                'friend_id' => $friendId,
                'status'    => 1,
            ]);
    
            return true;
        }
    
        // If exists → return status
        return (int) $record->status === 1;
    }

}
