<?php

namespace App\Services;

use App\Repositories\Eloquent\UserRepository;
use Exception;
use Carbon\Carbon;

class HomeService
{
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Get users for home screen with filters and sorting
     */
    public function getHomeUsers(array $filters, $currentUserId)
    {
        try {
            // Build where conditions for filtering
            $whereConditions = [
                ['is_active', '>=', 7], // Only completed profiles
                ['is_approve', '!=', 1], // Exclude blocked users
                ['id', '!=', $currentUserId],
            ];
            

            // Gender filter
            if ($filters['gender'] !== 'all') {
                $whereConditions[] = ['gender', '=', $filters['gender']];
            }

            // Country filter - handle multiple country codes
            if (!empty($filters['country_code']) && strtolower($filters['country_code']) !== 'all') {
                $countryCodes = $filters['country_code'];
                
                // If comma-separated string, convert to array
                if (!is_array($countryCodes)) {
                    $countryCodes = explode(',', $countryCodes);
                }

                // Trim spaces and make sure all codes are uppercase
                $countryCodes = array_map('trim', $countryCodes);
                $countryCodes = array_map('strtoupper', $countryCodes);

                // Add whereIn condition - fix the format
                $whereConditions[] = ['country_code', 'IN', $countryCodes];
            }
           $swissNowFormatted = convertTimezone(
                Carbon::now(),
                null,
                'Y-m-d H:i:s'
            );
            $whereConditions[] = function ($q) use ($swissNowFormatted) {
                $q->whereNull('gost_expire')
                ->orWhere('gost_expire', '<', $swissNowFormatted);
            };
           

            // Age filter - calculate date ranges
            if ($filters['min_age'] > 0 || $filters['max_age'] < 100) {
                $maxBirthDate = Carbon::now()->subYears($filters['min_age'])->format('Y-m-d');
                $minBirthDate = Carbon::now()->subYears($filters['max_age'] + 1)->addDay()->format('Y-m-d');

                $whereConditions[] = ['date_of_birth', '<=', $maxBirthDate];
                $whereConditions[] = ['date_of_birth', '>=', $minBirthDate];
            }

            
            // Order by for sorting - check if random is requested
            $orderBy = $this->buildOrderBy($filters['sort_by'], $filters['random'] ?? 1);
            
            // Select columns
            $columns = ['id', 'name', 'date_of_birth','last_seen_at', 'country_code', 'gender', 'images', 'lat', 'lng', 'created_at','booster_ranking','gost_expire','boost'];

            // Check if pagination is requested
            // if (isset($filters['per_page']) && $filters['per_page'] > 0) {

                $perPage = $filters['per_page'] ?? 20; // Max 20 per page
                // $users = $this->userRepo->getDataWithPagination(
                //     $whereConditions,
                //     ['pendingSentRequests', 'pendingReceivedRequests', 'acceptedFriendships', 'sentFriendRequests', 'receivedFriendRequests', 'blocks', 'blockedBy'],
                //     $columns,
                //     [],
                //     $orderBy,
                //     $perPage,
                //     $filters['page']
                // );
                $users = $this->userRepo->getDataWithPagination(
                    $whereConditions,
                    ['pendingSentRequests', 'pendingReceivedRequests', 'sentFriendRequests', 'receivedFriendRequests', 'blocks', 'blockedBy','lastShipping'],
                    $columns,
                    [],
                    $orderBy,
                    $perPage,
                    $filters['page']
                );
                // [
                    //     ['nonFriends', $currentUserId] // 👈 APPLY SCOPE
                    // ]

                
                // Filter out blocked users (users blocked by current user OR users who blocked current user)
                $filteredUsers = $users->getCollection()->filter(function ($user) use ($currentUserId) {
                    checkBoosterActive($currentUserId);
                    // Check if current user blocked this user
                    $currentUserBlockedThisUser = $user->blockedBy && $user->blockedBy->contains('blocker_id', $currentUserId);
                    
                    // Check if this user blocked current user
                    $thisUserBlockedCurrentUser = $user->blocks && $user->blocks->contains('blocked_id', $currentUserId);
                    
                    // Return true only if neither blocked each other
                    return !$currentUserBlockedThisUser && !$thisUserBlockedCurrentUser;
                });

                $processedUsers = $filteredUsers->map(function ($user) use ($currentUserId) {
                    return $this->processUserData($user, $currentUserId);
                });

                
                
                $processedUsers = $processedUsers->sort(function ($a, $b) {
                    $aRank = (int) ($a['booster_ranking'] ?? 0);
                    $bRank = (int) ($b['booster_ranking'] ?? 0);

                    $aBoost = $aRank > 0;
                    $bBoost = $bRank > 0;

                    // boosted first
                    if ($aBoost !== $bBoost) {
                        return $aBoost ? -1 : 1;
                    }

                    // among boosted: rank asc
                    if ($aBoost && $bBoost && $aRank !== $bRank) {
                        return $aRank <=> $bRank;
                    }

                    // keep existing order for normal users (stable/no change)
                    return 0;
                })->values();

                // Recalculate pagination after filtering
                $total = $processedUsers->count();
                $lastPage = ceil($total / $perPage);
                return [
                    'data' => [
                        'users' => $processedUsers->values()->all(),
                        'pagination' => [
                            'current_page' => $users->currentPage(),
                            'per_page' => $perPage,
                            'total' => $total,
                            'last_page' => $lastPage,
                            'has_more' => $users->currentPage() < $lastPage
                        ],
                    ],
                    'message' => __('message.users_fetched_successfully')
                ];
        } catch (Exception $e) {
            throw new Exception('Failed to fetch home users: ' . $e->getMessage());
        }
    }

    /**
     * Process individual user data
     */
    private function processUserData($user, $currentUserId)
    {

      
        // Calculate age from date of birth
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : null;

        // Process images
        $images = [];
        // if ($user->images) {
        //     $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
        //     if (is_array($userImages)) {
        //         $images = array_map(function ($imagePath) {
        //             return asset('storage/' . $imagePath);
        //         }, $userImages);
        //     }
        // }
        
        $images = [];

        if (!empty($user->images)) {
            $userImages = is_string($user->images)
                ? json_decode($user->images, true)
                : $user->images;
        
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
        
                    // already a full URL
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        return $imagePath;
                    }
        
                    // stored relative path
                    return asset('storage/' . ltrim($imagePath, '/'));
        
                }, $userImages);
            }
        }


        // Determine friend_status
        $friendStatus = $this->determineFriendStatus($user, $currentUserId);

        // Determine block_status (1 if current user blocked this user, 0 otherwise)
        $blockStatus = $user->blockedBy->contains('blocker_id', $currentUserId) ? 1 : 0;
        return [
            'id' => $user->id,
            'name' => $user->name,
            'age' => $age,
            'date_of_birth' => $user->date_of_birth,
            'country_code' => $user->country_code,
            'images' => $images,
            'lat' => (float) $user->lat,
            'lng' => (float) $user->lng,
            'last_seen_at' => $user->last_seen_at,
            'gender' => $user->gender,
            'friend_status' => $friendStatus,
            'block_status' => $blockStatus,
            'booster_ranking' => $user->booster_ranking,
            'booster_expire' => $user->lastShipping && $user->lastShipping->end_date_time
                ? Carbon::parse($user->lastShipping->end_date_time)->toDateTimeString()
                : null
        ];
    }

    /**
     * Build order by array based on sort criteria
     */
    private function buildOrderBy($sortBy, $random = 1)
    {
        // If random is requested, use DB::raw for random ordering
        if ($random == 1) {
            return [\DB::raw('RAND()') => '']; // For MySQL
            // For PostgreSQL use: return [\DB::raw('RANDOM()') => ''];
        }

        switch ($sortBy) {
            case 'age':
                return ['date_of_birth' => 'desc']; // Newest birth date = youngest
            case 'recent':
                return ['created_at' => 'desc'];
            case 'distance':
            default:
                return ['id' => 'desc']; // Default fallback
        }
    }

    /**
     * Get users for map screen with distance-based filtering and sorting
     */
    public function getMapUsers(array $filters, $currentUserId)
    {
        try {
            $getuserData = $this->userRepo->find($currentUserId);
            // Build where conditions for filtering
            $whereConditions = [
                ['is_active', '>=', 7], // Only completed profiles
                ['is_approve', '!=', 1], // Exclude blocked users
                ['id', '!=', $currentUserId],
                ['lat', '!=', null], // Must have location
                ['lng', '!=', null],
                ['country_code', '=', $getuserData->country_code]
            ];

            $swissNowFormatted = convertTimezone(
                Carbon::now(),
                null,
                'Y-m-d H:i:s'
            );
            $whereConditions[] = function ($q) use ($swissNowFormatted) {
                $q->whereNull('gost_expire')
                ->orWhere('gost_expire', '<', $swissNowFormatted);
            };

            // Gender filter
            if ($filters['gender'] !== 'all') {
                $whereConditions[] = ['gender', '=', $filters['gender']];
            }

            // Age filter - calculate date ranges
            if ($filters['min_age'] > 0 || $filters['max_age'] < 100) {
                $maxBirthDate = Carbon::now()->subYears($filters['min_age'])->format('Y-m-d');
                $minBirthDate = Carbon::now()->subYears($filters['max_age'] + 1)->addDay()->format('Y-m-d');

                $whereConditions[] = ['date_of_birth', '<=', $maxBirthDate];
                $whereConditions[] = ['date_of_birth', '>=', $minBirthDate];
            }
            // Select columns
            $columns = ['id', 'name', 'date_of_birth', 'country_code', 'gender', 'images', 'lat', 'lng', 'created_at','booster_ranking','gost_expire','boost'];
            // Use getDataWithPagination to get larger dataset for distance filtering
            $perPage = 100; // Get more records to filter by distance
            $users = $this->userRepo->getDataWithPagination(
                $whereConditions,
                ['pendingSentRequests', 'pendingReceivedRequests', 'acceptedFriendships', 'sentFriendRequests', 'receivedFriendRequests', 'blocks', 'blockedBy','lastShipping'],
                $columns,
                [],
                ['id' => 'desc'],
                $perPage,
                1 // Always get first page to have more data for distance filtering
            );

            
            // Filter out blocked users (users blocked by current user OR users who blocked current user)
            $filteredBlockedUsers = $users->getCollection()->filter(function ($user) use ($currentUserId) {
                checkBoosterActive($currentUserId);
                // Check if current user blocked this user
                $currentUserBlockedThisUser = $user->blockedBy && $user->blockedBy->contains('blocker_id', $currentUserId);
                
                // Check if this user blocked current user
                $thisUserBlockedCurrentUser = $user->blocks && $user->blocks->contains('blocked_id', $currentUserId);
                
                // Return true only if neither blocked each other
                return !$currentUserBlockedThisUser && !$thisUserBlockedCurrentUser;
            });

            // Process users and calculate distances
            $processedUsers = $filteredBlockedUsers->map(function ($user) use ($filters, $currentUserId) {
                return $this->processMapUserData($user, $filters, $currentUserId);
            });

            // Filter by distance
            $filteredUsers = $processedUsers->filter(function ($user) use ($filters) {
                $distance = $user['distance'];
                return $distance >= $filters['min_distance'] && $distance <= $filters['max_distance'];
            });

            $filteredUsers = $filteredUsers->sortBy(function ($user) use ($filters) {

                // 1️⃣ Booster priority (boosted users first)
                // booster_ranking: 1,2,3... are higher priority than 0/null
                $boosterPriority = isset($user['booster_ranking']) && $user['booster_ranking'] > 0
                    ? $user['booster_ranking']
                    : PHP_INT_MAX;

                // 2️⃣ Secondary sorting based on filter
                return match ($filters['order_by'] ?? null) {
                    'nearest' => [$boosterPriority, $user['distance'] ?? PHP_INT_MAX],
                    'age'     => [$boosterPriority, $user['age'] ?? PHP_INT_MAX],
                    'recent'  => [$boosterPriority, -strtotime($user['created_at'] ?? '1970-01-01')],
                    default   => [$boosterPriority],
                };
            });


            // Manual pagination after distance filtering
            $requestedPerPage = $filters['per_page'];
            $currentPage = $filters['page'];
            $total = $filteredUsers->count();
            $lastPage = ceil($total / $requestedPerPage);
            
            $paginatedUsers = $filteredUsers->slice(($currentPage - 1) * $requestedPerPage, $requestedPerPage)->values();

            return [
                'data' => [
                    'users' => $paginatedUsers,
                    'pagination' => [
                        'current_page' => $currentPage,
                        'per_page' => $requestedPerPage,
                        'total' => $total,
                        'last_page' => $lastPage,
                        'has_more' => $currentPage < $lastPage
                    ],
                ],
                'message' => __('message.map_users_fetched_successfully')
            ];
        } catch (Exception $e) {
            dd($e);
            throw new Exception('Failed to fetch map users: ' . $e->getMessage());
        }
    }

    /**
     * Process individual user data for map with distance calculation
     */
    private function processMapUserData($user, $filters, $currentUserId)
    {
        // Calculate age from date of birth
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : null;

        // Calculate distance from current user
        $distance = $this->calculateDistance(
            $filters['user_lat'], 
            $filters['user_lng'], 
            (float) $user->lat, 
            (float) $user->lng
        );

        // Process images
        // $images = [];
        // if ($user->images) {
        //     $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
        //     if (is_array($userImages)) {
        //         $images = array_map(function ($imagePath) {
        //             return asset('storage/' . $imagePath);
        //         }, $userImages);
        //     }
        // }
        $images = [];

        if (!empty($user->images)) {
            $userImages = is_string($user->images)
                ? json_decode($user->images, true)
                : $user->images;
        
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
        
                    // already a full URL
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        return $imagePath;
                    }
        
                    // stored relative path
                    return asset('storage/' . ltrim($imagePath, '/'));
        
                }, $userImages);
            }
        }

        // Determine friend_status
        $friendStatus = $this->determineFriendStatus($user, $currentUserId);

        // Determine block_status (1 if current user blocked this user, 0 otherwise)
        $blockStatus = $user->blockedBy->contains('blocker_id', $currentUserId) ? 1 : 0;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'age' => $age,
            'date_of_birth' => $user->date_of_birth,
            'country_code' => $user->country_code,
            'images' => $images,
            'lat' => (float) $user->lat,
            'lng' => (float) $user->lng,
            'gender' => $user->gender,
            'distance' => round($distance, 2),
            'created_at' => $user->created_at,
            'friend_status' => $friendStatus,
            'block_status' => $blockStatus,
            'booster_ranking' => $user->booster_ranking,
            'booster_expire' => $user->lastShipping && $user->lastShipping->end_date_time
                ? Carbon::parse($user->lastShipping->end_date_time)->toDateTimeString()
                : null
        ];
    }

    /**
     * Determine friend status between current user and target user
     * 0 => nothing
     * 1 => current user sent pending request
     * 2 => friends (accepted)
     * 3 => target user sent pending request to current user
     */
    private function determineFriendStatus($user, $currentUserId)
    {
        // Check if they are friends (accepted friendship in EITHER direction)
        // Check in acceptedFriendships where this user is user_id
        $isFriendAsUser = $user->acceptedFriendships && $user->acceptedFriendships->contains(function ($friendship) use ($currentUserId, $user) {
            return $friendship->user_id == $user->id && $friendship->friend_id == $currentUserId;
        });

        // Check in receivedFriendRequests where user_id is currentUserId and status is accepted
        $isFriendAsFriend = $user->receivedFriendRequests && $user->receivedFriendRequests->contains(function ($friendship) use ($currentUserId) {
            return $friendship->user_id == $currentUserId && $friendship->status == 'accepted';
        });

        if ($isFriendAsUser || $isFriendAsFriend) {
            return 2; // Friends
        }

        // Check if current user sent a pending request to this user
        $currentUserSentRequest = $user->receivedFriendRequests && $user->receivedFriendRequests->contains(function ($friendship) use ($currentUserId) {
            return $friendship->user_id == $currentUserId && $friendship->status == 'pending';
        });

        if ($currentUserSentRequest) {
            return 1; // Current user sent request (pending)
        }

        // Check if this user sent a pending request to current user
        $userSentRequest = $user->sentFriendRequests && $user->sentFriendRequests->contains(function ($friendship) use ($currentUserId) {
            return $friendship->friend_id == $currentUserId && $friendship->status == 'pending';
        });

        if ($userSentRequest) {
            return 3; // This user sent request to current user (pending)
        }

        // No relationship
        return 0;
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        if (($lat1 == $lat2) && ($lng1 == $lng2)) {
            return 0;
        }

        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $kilometers = $miles * 1.609344;

        return $kilometers;
    }
    
    
    public function updateUserPlan($planId)
    {
        try {
            switch ($planId) {
                case 1:
                    echo 'Pending';
                    break;
            
                case 2:
                    $this->updateGhostPlan();
                    break;
            
                case 3:
                    echo 'Rejected';
                    break;
            
                default:
                    echo 'Unknown status';
            }

        } catch (Exception $e) {
            throw new Exception('Failed to fetch map users: ' . $e->getMessage());
        }
    }
    
    private function updateGhostPlan()
    {
        $user_id=getUserId();
        $data=[
                "gost_expire"=>null,
                "ghost"=>0
            ];
        $this->userRepo->updateOrCreate(['id'=>$user_id],$data);
    }
}
