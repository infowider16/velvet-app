<?php

namespace App\Services;

use App\Repositories\Eloquent\FriendshipRepository;
use App\Repositories\Eloquent\MessageRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\GroupRepository;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class FriendshipService
{
    protected $friendshipRepo;
    protected $userRepo;
    protected $groupRepo;
    protected $messageRepository;

    public function __construct(FriendshipRepository $friendshipRepo, UserRepository $userRepo, GroupRepository $groupRepo, MessageRepository $messageRepository)
    {
        $this->friendshipRepo = $friendshipRepo;
        $this->messageRepository = $messageRepository;
        $this->userRepo = $userRepo;
        $this->groupRepo = $groupRepo;
    }

    public function sendFriendRequest($userId, $friendId)
    {
        try {

            // Check if trying to send request to self
            if ($userId == $friendId) {
                throw new Exception(__('message.you_cannot_send_a_friend_request_to_yourself'));
            }

            // Check if friend exists
            $friend = $this->userRepo->find($friendId);
            if (!$friend) {
                throw new Exception(__('message.user_not_found'));
            }

            // Check if already friends or pending request exists - use 'first' method
            $existingRequest = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $userId],
                    ['friend_id', '=', $friendId]
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );

            if ($existingRequest) {
                if ($existingRequest->status === 'pending') {
                    throw new Exception(__('message.friend_request_already_sent'));
                } elseif ($existingRequest->status === 'accepted') {
                    throw new Exception(__('message.you_are_already_friends'));
                }
            }

            // Check if reverse request exists - use 'first' method
            $reverseRequest = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $friendId],
                    ['friend_id', '=', $userId]
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );

            if ($reverseRequest && $reverseRequest->status === 'pending') {
                throw new Exception(__('message.this_user_has_already_sent_you_a_friend_request'));
            }

            // Create friend request
            $friendship = $this->friendshipRepo->create([
                'user_id' => $userId,
                'friend_id' => $friendId,
                'status' => 'pending'
            ]);

            $sender = $this->userRepo->find($userId);
            $receiver = $this->userRepo->find($friendId);

            $titleEn = __('message.new_friend_request', [], 'en');
            $titleGe = __('message.new_friend_request', [], 'ge');

            $bodyEn = $sender
                ? ($sender->name . ' ' . __('message.sent_you_a_friend_request', [], 'en'))
                : __('message.you_have_a_new_friend_request', [], 'en');

            $bodyGe = $sender
                ? ($sender->name . ' ' . __('message.sent_you_a_friend_request', [], 'ge'))
                : __('message.you_have_a_new_friend_request', [], 'ge');

            $title = $titleEn;
            $body = $bodyEn;

            $titleTranslation = [
                'en' => $titleEn,
                'ge' => $titleGe,
            ];

            $bodyTranslation = [
                'en' => $bodyEn,
                'ge' => $bodyGe,
            ];

            $other = [
                'type' => 'friend_request',
                'user_id' => $userId,
                'screen_name' => 'user_profile'
            ];

            // Insert notification in DB
            try {
                $this->userRepo->createMobileNotification(
                    $userId,
                    $friendId,
                    $title,
                    $body,
                    $other,
                    $titleTranslation,
                    $bodyTranslation
                );
            } catch (\Throwable $e) {
                \Log::error('Error in createMobileNotification (sendFriendRequest): ' . $e->getMessage());
            }

            // Send push notification (if device tokens available)
            try {
                if (function_exists('sendPushNotification') && $receiver && !empty($receiver->device_token)) {
                    sendPushNotification([$receiver->device_token], $title, $body, $other, [$receiver->id], 'friend_requests');
                }
            } catch (\Throwable $e) {
                \Log::error('Error in sendPushNotification (sendFriendRequest): ' . $e->getMessage());
            }

            return [
                'data' => [
                    'friendship_id' => $friendship->id,
                    'status' => $friendship->status
                ],
                'message' => __('message.friend_request_sent_successfully')
            ];
        } catch (Exception $e) {
            \Log::error(__('message.failed_to_send_friend_request') . $e->getMessage());
            throw new Exception(__('message.failed_to_send_friend_request'));
        }
    }
    
    public function acceptFriendRequest($userId, $friendId)
    {
        try {
            $friendship = $this->friendshipRepo->getOneData([
                ['user_id', '=', $friendId],
                ['friend_id', '=', $userId],
                ['status', '=', 'pending']
            ]);
    
            if (!$friendship) {
                throw new Exception(__('message.friend_request_not_found_or_already_processed'));
            }
    
            $updated = $this->friendshipRepo->update(
                ['id' => $friendship->id],
                ['status' => 'accepted']
            );
    
            if (!$updated) {
                throw new Exception(__('message.failed_to_accept_friend_request'));
            }
    
            $receiver = $this->userRepo->find($userId);
            $sender = $this->userRepo->find($friendId);
    
            $titleEn = __('message.friend_request_accepted', [], 'en');
            $titleDe = __('message.friend_request_accepted', [], 'ge');
    
            $acceptedTextEn = __('message.accepted_your_friend_request', [], 'en');
            $acceptedTextDe = __('message.accepted_your_friend_request', [], 'ge');
    
            $fallbackBodyEn = __('message.your_friend_request_was_accepted', [], 'en');
            $fallbackBodyDe = __('message.your_friend_request_was_accepted', [], 'ge');
    
            $bodyEn = $receiver
                ? ($receiver->name . ' ' . $acceptedTextEn)
                : $fallbackBodyEn;
    
            $bodyDe = $receiver
                ? ($receiver->name . ' ' . $acceptedTextDe)
                : $fallbackBodyDe;
    
            $title = $titleEn;
            $body = $bodyEn;
    
            $titleTranslation = [
                'en' => $titleEn,
                'ge' => $titleDe,
            ];
    
            $bodyTranslation = [
                'en' => $bodyEn,
                'ge' => $bodyDe,
            ];
    
            $other = [
                'type' => 'friend_request_accepted',
                'user_id' => $userId,
                'screen_name' => 'user_profile',
            ];
    
            $this->messageRepository->getNotificationStatus($userId, $friendId);
    
            try {
                $this->userRepo->createMobileNotification(
                    $userId,
                    $friendId,
                    $title,
                    $body,
                    $other,
                    $titleTranslation,
                    $bodyTranslation
                );
            } catch (\Throwable $e) {
                Log::error('Error in createMobileNotification (acceptFriendRequest): ' . $e->getMessage());
            }
    
            try {
                if (function_exists('sendPushNotification') && $sender && !empty($sender->device_token)) {
                    sendPushNotification([$sender->device_token], $title, $body, $other, [$receiver?->id], 'friend_requests');
                }
            } catch (\Throwable $e) {
                Log::error('Error in sendPushNotification (acceptFriendRequest): ' . $e->getMessage());
            }
    
            //also add user to message table for both users so that chat will be available immediately after accepting friend request
            $this->addUserToMessageTable($userId, $friendId);
            return [
                'data' => [
                    'friendship_id' => $friendship->id,
                    'status' => 'accepted',
                ],
                'message' => __('message.friend_request_accepted_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('acceptFriendRequest error: ' . $e->getMessage(), [
                'user_id' => $userId,
                'friend_id' => $friendId,
            ]);
    
            throw new Exception(__('message.failed_to_accept_friend_request'));
        }
    }

    private function addUserToMessageTable($userId, $friendId)
    {
        // Create empty chat entry so users appear in message list
        try {
            $existingChat = $this->messageRepository->getOneData([
                ['sender_id', '=', $userId],
                ['receiver_id', '=', $friendId],
            ]);

            if (!$existingChat) {
                $existingChat = $this->messageRepository->getOneData([
                    ['sender_id', '=', $friendId],
                    ['receiver_id', '=', $userId],
                ]);
            }

            if (!$existingChat) {
                $this->messageRepository->create([
                    'sender_id' => $userId,       
                    'receiver_id' => $friendId,   
                    'group_id' => null,
                    'message_text' => null,
                    'media_type' => null,
                    'status' => 'sent',
                ]);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Error creating empty chat entry (acceptFriendRequest): ' . $e->getMessage(), [
                'user_id' => $userId,
                'friend_id' => $friendId,
            ]);
        }
    }

    public function deleteFriendRequest($userId, $friendId)
    {
        try {
            // Check both directions (sent or received)
            $friendship = $this->friendshipRepo->getOneData([
                ['user_id', '=', $userId],
                ['friend_id', '=', $friendId]
            ]);

            if (!$friendship) {
                $friendship = $this->friendshipRepo->getOneData([
                    ['user_id', '=', $friendId],
                    ['friend_id', '=', $userId]
                ]);
            }

            if (!$friendship) {
                throw new Exception(__('message.friendship_not_found'));
            }

            // Delete the friendship
            $deleted = $this->friendshipRepo->deleteData(['id' => $friendship->id]);

            if (!$deleted) {
                throw new Exception(__('message.failed_to_delete_friend_request'));
            }

            $otherUserId = ($friendship->user_id == $userId) ? $friendship->friend_id : $friendship->user_id;
            $otherUser = $this->userRepo->find($otherUserId);
            $actor = $this->userRepo->find($userId);
            $title = __('message.friend_request_cancelled');
            $body = $actor ? __('message.friend_request_rejected_by_user', ['name' => $actor->name]) : __('message.friend_request_rejected');
            $other = ['type' => 'friend_request_cancelled', 'user_id' => $userId, 'screen_name' => 'user_profile'];

            // Send notification to the other user (rejection/cancel)
            // try {
            //     $this->userRepo->createMobileNotification($userId, $otherUserId, $title, $body, $other);
            // } catch (\Throwable $e) {
            //     \Log::error('Error in createMobileNotification (deleteFriendRequest): ' . $e->getMessage());
            // }

            // try {
            //     if (function_exists('sendPushNotification') && $otherUser && !empty($otherUser->device_token)) {
            //         sendPushNotification([$otherUser->device_token], $title, $body, $other);
            //     }
            // } catch (\Throwable $e) {
            //     \Log::error('Error in sendPushNotification (deleteFriendRequest): ' . $e->getMessage());
            // }

            // If it was accepted, delete the reverse relationship too
            if ($friendship->status === 'accepted') {
                $reverseFriendship = $this->friendshipRepo->getOneData([
                    ['user_id', '=', $friendId],
                    ['friend_id', '=', $userId]
                ]);
                if ($reverseFriendship) {
                    $this->friendshipRepo->deleteData(['id' => $reverseFriendship->id]);
                }
            }

            return [
                'message' => __('message.friend_request_deleted_successfully')
            ];
        } catch (Exception $e) {
            \Log::error(__('message.failed_to_delete_friend_request') . $e->getMessage());
            throw new Exception(__('message.failed_to_delete_friend_request'));
        }
    }

    public function getPendingRequests($userId, $request)
    {
        try {


            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 20);

            // Get requests where current user is the RECEIVER (friend_id)
            $friends = $this->friendshipRepo->getDataWithPagination(
                [
                    ['friend_id', '=', $userId],
                    ['status', '=', 'pending']
                ],
                ['user'], // Load the user who sent the request
                ['*'],
                [],
                ['id' => 'desc'],
                $perPage,
                $page
            );


            $processedUsers = $friends->getCollection()->map(function ($user) use ($userId) {
                return $this->processUserData($user->user, $userId);
            });

            $friends->setCollection($processedUsers);

            return [
                'data' => [
                    'users' => $friends->items(),
                    'pagination' => [
                        'current_page' => $friends->currentPage(),
                        'per_page' => $friends->perPage(),
                        'total' => $friends->total(),
                        'last_page' => $friends->lastPage(),
                        'has_more' => $friends->hasMorePages()
                    ],
                ],
                'message' => __('message.pending_requests_fetched_successfully')
            ];
        } catch (Exception $e) {
            throw new Exception(__('message.failed_to_fetch_pending_requests') . ': ' . $e->getMessage());
        }
    }

    public function getSentRequests($userId, $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            // Get requests where current user is the SENDER (user_id)
            $requests = $this->friendshipRepo->getDataWithPagination(
                [
                    ['user_id', '=', $userId],
                    ['status', '=', 'pending']
                ],
                ['friend'], // Load the friend who will receive the request
                ['*'],
                [],
                ['id' => 'desc'],
                $perPage,
                $page
            );

            return [
                'data' => $requests,
                'message' => __('message.sent_requests_fetched_successfully')
            ];
        } catch (Exception $e) {
            throw new Exception(__('message.failed_to_fetch_sent_requests') . ': ' . $e->getMessage());
        }
    }

    public function getFriendsList($userId, $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            // Step 1: Only "accepted" records fetch using repo
            $friends = $this->friendshipRepo->getDataWithPagination(
                [
                    ['status', '=', 'accepted']
                ],
                ['user', 'friend', 'user.blocks', 'user.blockedBy', 'friend.blocks', 'friend.blockedBy'],
                ['*'],
                [],
                ['id' => 'desc'],
                $perPage,
                $page
            );

            // Step 2: Filter OR condition manually (user_id = X OR friend_id = X)
            $filtered = $friends->getCollection()->filter(function ($item) use ($userId) {
                // Exclude if user has blocked friend or friend has blocked user
                $friendUser = ($item->user_id == $userId) ? $item->friend : $item->user;
                if (!$friendUser || $friendUser->id == $userId) {
                    return false;
                }
                // Check if current user has blocked this friend
                if (
                    ($friendUser->blockedBy && $friendUser->blockedBy->contains('blocker_id', $userId)) ||
                    ($friendUser->blocks && $friendUser->blocks->contains('blocked_id', $userId))
                ) {
                    return false;
                }
                // Also check if current user has blocked this friend (redundant, but safe)
                $hasBlocked = $this->friendshipRepo->isBlocked($userId, $friendUser->id);
                $blockedBy = $this->friendshipRepo->isBlocked($friendUser->id, $userId);
                if ($hasBlocked || $blockedBy) {
                    return false;
                }
                return $item->user_id == $userId || $item->friend_id == $userId;
            });

            // Step 3: Map friend user
            $processedUsers = $filtered->map(function ($friendship) use ($userId) {
                $friendUser = ($friendship->user_id == $userId)
                    ? $friendship->friend
                    : $friendship->user;

                if ($friendUser->id == $userId) {
                    return null;
                }

                return $this->processFriendUserData($friendUser, $userId);
            })->filter()->values();

            // Pagination adjust
            $total = $processedUsers->count();
            $lastPage = ceil($total / $perPage);

            return [
                'data' => [
                    'users' => $processedUsers,
                    'pagination' => [
                        'current_page' => $friends->currentPage(),
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => $lastPage,
                        'has_more' => $friends->currentPage() < $lastPage,
                    ],
                ],
                'message' => __('message.friends_list_fetched_successfully'),
            ];
        } catch (Exception $e) {
            throw new Exception(__('message.failed_to_fetch_friends_list') . ': ' . $e->getMessage());
        }
    }

    public function blockUser($userId, $blockedUserId)
    {
        try {
            // Check if trying to block self
            if ($userId == $blockedUserId) {
                throw new Exception(__('message.you_cannot_block_yourself'));
            }

            // Check if user exists
            $blockedUser = $this->userRepo->find($blockedUserId);
            if (!$blockedUser) {
                throw new Exception(__('message.user_not_found'));
            }

            // Check if already blocked
            $existingBlock = $this->friendshipRepo->isBlocked($userId, $blockedUserId);
            if ($existingBlock) {
                throw new Exception(__('message.user_is_already_blocked'));
            }

            // Create block
            $block = $this->friendshipRepo->createBlock([
                'blocker_id' => $userId,
                'blocked_id' => $blockedUserId
            ]);

            // Remove any existing friendships in both directions
            $friendship1 = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $userId],
                    ['friend_id', '=', $blockedUserId]
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );

            if ($friendship1) {
                $this->friendshipRepo->deleteData(['id' => $friendship1->id]);
            }

            $friendship2 = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $blockedUserId],
                    ['friend_id', '=', $userId]
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );

            if ($friendship2) {
                $this->friendshipRepo->deleteData(['id' => $friendship2->id]);
            }

            // --- Block in all groups where blockedUserId is a member (status != 2) ---
            // if (property_exists($this, 'groupRepo') && $this->groupRepo) {
            //     $this->groupRepo->groupMemberModel
            //         ->where('user_id', $blockedUserId)
            //         ->where('status', '!=', 2) // not left
            //         ->update(['status' => 1]); // 1 = blocked
            // }
            $this->messageRepository->deleteChat($userId, $blockedUserId);
            // --- END group block logic ---

            return [
                'data' => [
                    'block_id' => $block->id,
                    'blocked_user_id' => $blockedUserId
                ],
                'message' => __('message.user_blocked_successfully')
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to block user: ' . $e->getMessage());
        }
    }

    public function unblockUser($userId, $blockedUserId)
    {
        try {
            // Find the block
            $block = $this->friendshipRepo->findBlockByUsers($userId, $blockedUserId);

            if (!$block) {
                throw new Exception(__('message.bloc_user_not_found'));
            }

            // Delete the block
            $deleted = $this->friendshipRepo->deleteBlock($block->id);

            if (!$deleted) {
                throw new Exception(__('message.failed_to_unblock_user'));
            }

            // Restore friendship if both users were friends before block (i.e., both had accepted friendship)
            $friendship1 = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $userId],
                    ['friend_id', '=', $blockedUserId],
                    ['status', '=', 'accepted']
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );
            $friendship2 = $this->friendshipRepo->getByWhere(
                [
                    ['user_id', '=', $blockedUserId],
                    ['friend_id', '=', $userId],
                    ['status', '=', 'accepted']
                ],
                ['id' => 'desc'],
                ['*'],
                [],
                [],
                'first'
            );

            // If no friendship exists in either direction, restore it as accepted
            if (!$friendship1 && !$friendship2) {
                $this->friendshipRepo->create([
                    'user_id' => $userId,
                    'friend_id' => $blockedUserId,
                    'status' => 'accepted'
                ]);
            }

            // --- Unblock in all groups where $blockedUserId is a member and status=1 (blocked), regardless of role ---
            if (property_exists($this, 'groupRepo') && $this->groupRepo) {
                $this->groupRepo->groupMemberModel
                    ->where('user_id', $blockedUserId)
                    ->where('status', 1)
                    ->update(['status' => 0]);
                $getGroupIds = $this->groupRepo->fetchAll(['user_id' => $userId, 'role' => "admin"], ['group_id'], 'get')->pluck('group_id')->toArray();
                $this->groupRepo->deleteData([
                    'where' => ['user_id' => $blockedUserId, 'role' => "member"],
                    'whereIn' => ['group_id' => $getGroupIds]
                ]);
            }
            // --- END group unblock logic ---
            $this->friendshipRepo->friendDelete(['user_id' => $userId, 'friend_id' => $blockedUserId]);

            // 
            return [
                'message' => __('message.user_unblocked_successfully')
            ];
        } catch (Exception $e) {
            throw new Exception('Failed to unblock user: ' . $e->getMessage());
        }
    }

    public function getBlockedUsersList($userId, $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $blockedUsers = $this->friendshipRepo->getBlockedUsersList($userId, $perPage, $page);
         
            $processedUsers = $blockedUsers->getCollection()->map(function ($user) use ($userId) {
                return $this->processBlockedUserData($user->blocked, $userId);
            });

            $blockedUsers->setCollection($processedUsers);
           

            return [
                'data' => [
                    'users' => $blockedUsers->items(),
                    'pagination' => [
                        'current_page' => $blockedUsers->currentPage(),
                        'per_page' => $blockedUsers->perPage(),
                        'total' => $blockedUsers->total(),
                        'last_page' => $blockedUsers->lastPage(),
                        'has_more' => $blockedUsers->hasMorePages()
                    ],
                ],
                'message' => __('message.blocked_users_fetched_successfully')
            ];
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch blocked users: ' . $e->getMessage());
        }
    }



    private function processUserData($user, $currentUserId)
    {

        // Calculate age from date of birth
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : null;

        // Process images
        $images = [];
        if ($user->images) {
            $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
                    return asset('storage/' . $imagePath);
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
            'is_delete' => $user->is_delete ?? 0,
            // 'friend_status' => $friendStatus,
            // 'block_status' => $blockStatus
        ];
    }

    // New method specifically for friend list
    private function processFriendUserData($user, $currentUserId)
    {
        // Calculate age from date of birth
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : null;

        // Process images
        $images = [];
        if ($user->images) {
            $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
                    return asset('storage/' . $imagePath);
                }, $userImages);
            }
        }

        // For friends list, friend_status is always 2 (accepted)
        $friendStatus = 2;

        // Determine block_status
        $blockStatus = 0;
        if ($user->blockedBy && $user->blockedBy->contains('blocker_id', $currentUserId)) {
            $blockStatus = 1; // Current user blocked this friend
        }


        return [
            'id' => $user->id,
            'name' => $user->name,
            'is_delete' => $user->is_delete,
            'age' => $age,
            'date_of_birth' => $user->date_of_birth,
            'country_code' => $user->country_code,
            'images' => $images,
            'lat' => (float) $user->lat,
            'lng' => (float) $user->lng,
            'gender' => $user->gender,
            'friend_status' => $friendStatus,
            'block_status' => $blockStatus
        ];
    }

    // New method specifically for blocked users list
    private function processBlockedUserData($user, $currentUserId)
    {
        // Calculate age from date of birth
        $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : null;

        // Process images
        $images = [];
        if ($user->images) {
            $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
                    return asset('storage/' . $imagePath);
                }, $userImages);
            }
        }

        // Determine friend_status - check if there was any friendship before blocking
        $friendStatus = 0;
        if ($user->acceptedFriendships) {
            $isFriend = $user->acceptedFriendships->contains(function ($friendship) use ($currentUserId) {
                return $friendship->user_id == $currentUserId || $friendship->friend_id == $currentUserId;
            });
            if ($isFriend) {
                $friendStatus = 2;
            }
        }

        // Check for pending requests
        if ($friendStatus === 0 && $user->pendingReceivedRequests) {
            $hasPendingReceived = $user->pendingReceivedRequests->contains(function ($friendship) use ($currentUserId) {
                return $friendship->user_id == $currentUserId;
            });
            if ($hasPendingReceived) {
                $friendStatus = 1;
            }
        }

        if ($friendStatus === 0 && $user->pendingSentRequests) {
            $hasPendingSent = $user->pendingSentRequests->contains(function ($friendship) use ($currentUserId) {
                return $friendship->friend_id == $currentUserId;
            });
            if ($hasPendingSent) {
                $friendStatus = 3;
            }
        }

        // For blocked users list, block_status is always 1 (current user blocked them)
        $blockStatus = 1;

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
            'friend_status' => $friendStatus,
            'block_status' => $blockStatus,
            'is_delete' => $user->is_delete ?? 0,
        ];
    }

    private function determineFriendStatus($user, $currentUserId)
    {

        // Check if they are friends (accepted friendship)
        // acceptedFriendships contains Friendship models where current user can be either user_id or friend_id
        $isFriend = $user->acceptedFriendships->contains(function ($friendship) use ($currentUserId) {
            return $friendship->user_id == $currentUserId || $friendship->friend_id == $currentUserId;
        });

        if ($isFriend) {
            return 2;
        }

        // Check if there's a pending request
        // pendingReceivedRequests: where user received request from currentUserId
        $hasPendingReceived = $user->pendingReceivedRequests->contains(function ($friendship) use ($currentUserId) {
            return 1;
        });

        // pendingSentRequests: where user sent request to currentUserId
        $hasPendingSent = $user->pendingSentRequests->contains(function ($friendship) use ($currentUserId) {
            return 3;
        });

        if ($hasPendingSent) {
            return 3;
        }
        if ($hasPendingReceived) {
            return 1;
        }

        // No relationship
        return 0;
    }

    public function getNotifications($userId, $request)
    {
        try {

            $readNotifications = $this->userRepo->markNotificationsAsRead($userId);

            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $notifications = $this->userRepo->getNotificationsWithPagination(
                [['receiver_user_id', '=', $userId]],
                $perPage,
                $page
            );

            // Optionally process notification data here if needed
            $processed = $notifications->getCollection()->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->title,
                    'body' => $notif->body,
                    'data' => json_decode($notif->other, true),
                    'created_at' => $notif->created_at,
                    // add more fields as needed
                ];
            });

            $notifications->setCollection($processed);

            return [
                'data' => [
                    'notifications' => $notifications->items(),
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                        'last_page' => $notifications->lastPage(),
                        'has_more' => $notifications->hasMorePages()
                    ],
                ],
                'message' => __('message.notifications_fetched_successfully')
            ];
        } catch (Exception $e) {
            throw new Exception(__('message.failed_to_fetch_notifications') . ': ' . $e->getMessage());
        }
    }
    public function checkUserBlocked($userId)
    {
        try {
            $loginUser=getUser();
            $blocked = $this->friendshipRepo->isBlocked($loginUser->id, $userId);
            return isset($blocked->id)?true:false;
        } catch (Exception $e) {
            throw new Exception(__('message.failed_to_fetch_blocked_status') . ': ' . $e->getMessage());
        }
    }
}
