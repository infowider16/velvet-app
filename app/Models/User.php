<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $arrays = [
        'images',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function getLocationConsentLabelAttribute()
    {
        return $this->location_consent ? 'Yes' : 'No';
    }

    public function getStatusLabelAttribute()
    {
        // You can expand this for more statuses if needed
        return $this->is_approve == 0 ? 'Unblocked' : 'Blocked';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->is_approve == 0 ? 'bg-success' : 'bg-danger';
    }



     // ============ RELATIONSHIPS ============

    // Friend requests sent by this user
    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    // Friend requests received by this user
    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    // ============ HELPER METHODS ============

    // Check if two users are friends
    public function isFriendWith($userId): bool
    {
        return Friendship::where(function ($q) use ($userId) {
            $q->where('user_id', $this->id)
              ->where('friend_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('friend_id', $this->id)
              ->where('user_id', $userId);
        })->where('status', 'accepted')->exists();
    }

    // Check if a friend request was sent
    public function hasSentFriendRequestTo($userId): bool
    {
        return Friendship::where('user_id', $this->id)
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }

    // Check if received a friend request
    public function hasReceivedFriendRequestFrom($userId): bool
    {
        return Friendship::where('user_id', $userId)
            ->where('friend_id', $this->id)
            ->where('status', 'pending')
            ->exists();
    }

    // Get all friends (accepted friendships)
    public function friends()
    {
        return User::whereIn('id', function ($query) {
            $query->select('friend_id')
                  ->from('friendships')
                  ->where('user_id', $this->id)
                  ->where('status', 'accepted')
                  ->union(
                      \DB::table('friendships')
                        ->select('user_id')
                        ->where('friend_id', $this->id)
                        ->where('status', 'accepted')
                  );
        });
    }

    // Get pending friend requests sent
    public function pendingSentRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id')
                    ->where('status', 'pending');
    }

    // Get pending friend requests received
    public function pendingReceivedRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id')
                    ->where('status', 'pending');
    }

    // Get accepted friendships
    public function acceptedFriendships(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id')
                    ->where('status', 'accepted');
    }
    

     public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    // Users who blocked this user
    public function blockedBy(): HasMany
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }


    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }
    
    public function boosttransactions()
    {
        return $this->belongsTo(Transaction::class, 'boost', 'id');
    }
    
    public function lastShipping()
    {
        return $this->hasOne(BoostHistory::class, 'user_id', 'id')
                    ->latestOfMany(); // latest by id
    }


    // ============ HELPER METHODS ============

    // Check if user has blocked another user
    public function hasBlocked($userId): bool
    {
        return Block::where('blocker_id', $this->id)
            ->where('blocked_id', $userId)
            ->exists();
    }

    // Check if user is blocked by another user
    public function isBlockedBy($userId): bool
    {
        return Block::where('blocker_id', $userId)
            ->where('blocked_id', $this->id)
            ->exists();
    }

    // Add accessor for last_seen_at in proper format
    public function getLastSeenAtAttribute($value)
    {
        if ($value) {
            try {
                return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return $value;
            }
        }
        return null;
    }
    
    public function scopeNonFriends(Builder $query, int $userId, bool $excludeBlocked = true): Builder
    {
        // Subquery: accepted friendship user_ids related to $userId (both directions)
        $acceptedFriendIds = DB::table('friendships')
            ->selectRaw("
                CASE
                    WHEN user_id = ? THEN friend_id
                    ELSE user_id
                END as friend_user_id
            ", [$userId])
            ->where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            });
    
        // Exclude: self + accepted friends
        $query->where('id', '!=', $userId)
              ->whereNotIn('id', $acceptedFriendIds);
    
        
    
        return $query;
    }
    
    public function boostHistory(): HasOne
    {
        return $this->hasOne(BoostHistory::class, 'boost', 'id');
    }
}
