<?php

namespace App\Services;

use App\Models\Block;
use App\Repositories\Eloquent\MessageRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\FriendshipRepository;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MessageService
{
    protected $messageRepo;
    protected $userRepo;
    protected $groupRepo;
    protected $friendshipRepo;

    public function __construct(MessageRepository $messageRepo, UserRepository $userRepo, GroupRepository $groupRepo, FriendshipRepository $friendshipRepo)
    {
        $this->messageRepo = $messageRepo;
        $this->userRepo = $userRepo;
        $this->groupRepo = $groupRepo;
        $this->friendshipRepo = $friendshipRepo;
    }



    public function sendMessage($senderId, $data)
    {
        try {
            // Validate receiver_id if present
            if (!empty($data['receiver_id'])) {
                $receiver = $this->userRepo->find($data['receiver_id']);
                if (empty($receiver)) {
                    return [
                        'data' => null,
                        'message' => __('message.receiver_user_does_not_exist')
                    ];
                }
            }

            // If group_id is set, receiver_id must be null
            if (!empty($data['group_id'])) {
                $data['receiver_id'] = null;
            }

            $messageData = [
                'sender_id' => $senderId,
                'receiver_id' => $data['receiver_id'] ?? null,
                'group_id' => $data['group_id'] ?? null,
                'message_text' => $data['message_text'] ?? null,
                'media_type' => $data['media_type'] ?? null,
                'status' => 'sent',
            ];

            // Attachments: media_url, thumbnail, duration, file_size, document_url, link_url
            if (isset($data['media_url'])) {
                $messageData['media_url'] = $data['media_url'];
            }
            if (isset($data['thumbnail'])) {
                $messageData['thumbnail'] = $data['thumbnail'];
            }
            if (isset($data['duration'])) {
                $messageData['duration'] = $data['duration'];
            }
            if (isset($data['file_size'])) {
                $messageData['file_size'] = $data['file_size'];
            }
            // New: document_url (for document attachments)
            if (isset($data['document_url'])) {
                $messageData['document_url'] = $data['document_url'];
            }
            // New: link_url (for link sharing)
            if (isset($data['link_url'])) {
                $messageData['link_url'] = $data['link_url'];
            }

            $message = $this->messageRepo->create($messageData);

            // Fix media_url and document_url in returned message data (always use asset('storage/'))
            // if ($message && $message->media_url) {
            //     $mediaUrl = $message->media_url;
            //     $message->media_url = asset('storage/' . $mediaUrl);
            // }
            // if ($message && isset($message->document_url) && $message->document_url) {
            //     $docUrl = $message->document_url;
            //     $message->document_url = asset('storage/' . $docUrl);
            // }

            // Send notification if this is the first message ever between these users (not group)
            if (!empty($data['receiver_id']) && empty($data['group_id'])) {
                $receiverId = $data['receiver_id'];
                // Check if this is the first message from sender to receiver (both directions)
                $existingMessages = $this->messageRepo->getByWhere(
                    [
                        ['sender_id', '=', $senderId],
                        ['receiver_id', '=', $receiverId]
                    ],
                    [],
                    ['id'],
                    [],
                    [],
                    'count'
                ) + $this->messageRepo->getByWhere(
                    [
                        ['sender_id', '=', $receiverId],
                        ['receiver_id', '=', $senderId]
                    ],
                    [],
                    ['id'],
                    [],
                    [],
                    'count'
                );

                if ($existingMessages == 1) { // Only this message exists
                    $receiver = $this->userRepo->find($receiverId);
                    $sender = $this->userRepo->find($senderId);
                    $title = __('message.new_message_title');
                    $body = $sender ? __('message.new_message_body_by_user', ['name' => $sender->name]) : __('message.new_message_body');
                    $other = ['type' => 'first_message', 'user_id' => $senderId, 'screen_name' => 'chat'];

                    try {
                        if (function_exists('sendPushNotification') && $receiver && !empty($receiver->device_token)) {
                            $checknotification=$this->messageRepo->getNotificationStatus($receiver->id,$sender->id);
                            if($checknotification){
                                sendPushNotification([$receiver->device_token], $title, $body, $other,[$receiver->id],'new_messages');
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::error('Error in sendPushNotification (firstMessage): ' . $e->getMessage());
                    }
                }
            }
            $mediaUrl = null;
                if ($message->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($message->media_url, '/'));
                } elseif ($message->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($message->document_url, '/'));
                } elseif ($message->link_url) {
                    $mediaUrl = $message->link_url;
                }
                $documentUrl = $message->document_url ? asset('storage/' . ltrim($message->document_url, '/')) : null;

                $message->media_url = $mediaUrl;
                $message->document_url = $documentUrl;
            return [
                'data' => $message,
                'message' => __('message.message_sent_successfully')
            ];
        } catch (Exception $e) {
           \Log::error(__('message.failed_to_send_message') . ': ' . $e->getMessage());
            throw new Exception(__('message.failed_to_send_message'));
        }
    }

    public function getGroupChatHistory($userId, $groupId, $request)
    {
        try {
            $perPage = $request['per_page'] ?? 20;
            $page = $request['page'] ?? 1;
            $messages = $this->messageRepo->getDataWithPagination(
                ['group_id' => $groupId],
                ['group', 'sender', 'receiver'],
                ['created_at', 'desc'],
                [],
                [],
                $perPage,
                $page
            );

            return [
                'data' => $messages,
                'message' => __('message.group_chat_history_retrieved_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to retrieve group chat history: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_retrieve_group_chat_history'));
        }
    }

    public function getMessages($userId, $otherUserId, $request)
    {
        try {
            $perPage = (int) ($request['per_page'] ?? 20);
            $page = (int) ($request['page'] ?? 1);

            // Mark all messages as read from otherUserId to userId
            $this->messageRepo->markMessagesAsRead($otherUserId, $userId);

            $where = [
                ['sender_id', '=', $userId],
                ['receiver_id', '=', $otherUserId]
            ];
            $whereReverse = [
                ['sender_id', '=', $otherUserId],
                ['receiver_id', '=', $userId]
            ];

            // Fetch all messages in both directions (no pagination here)
            $allMessages1 = $this->messageRepo->getByWhere(
                $where,
                ['created_at' => 'desc'],
                ['*'],
                ['sender', 'receiver'],
                [],
                'get'
            );
            $allMessages2 = $this->messageRepo->getByWhere(
                $whereReverse,
                ['created_at' => 'desc'],
                ['*'],
                ['sender', 'receiver'],
                [],
                'get'
            );

            // Merge and sort so that newest messages are at the top (descending by created_at)
            $allMessages = collect($allMessages1)
                ->merge($allMessages2)
                ->sortByDesc('created_at')
                ->values();

            $total = $allMessages->count();
            $lastPage = (int) ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginated = $allMessages->slice($offset, $perPage)->values();

            // Prepare the other user's minimal profile (name, images, last_seen_at)
            $otherUser = $this->userRepo->find($otherUserId);
            $otherUserProfile = null;
            if ($otherUser) {
                $profile = $this->processUserData($otherUser, $userId);
                // Format last_seen_at using accessor (already formatted in model)
                $otherUserProfile = [
                    'id' => $profile['id'],
                    'name' => $profile['name'],
                    'images' => $profile['images'],
                    'last_seen_at' => $profile['last_seen_at'] ?? null,
                ];
            }

            $processedMessages = $paginated->map(function ($msg) {
                 $mediaUrl = null;
                if ($msg->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->media_url, '/'));
                } elseif ($msg->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->document_url, '/'));
                } elseif ($msg->link_url) {
                    $mediaUrl = $msg->link_url;
                }
                $documentUrl = $msg->document_url ? asset('storage/' . ltrim($msg->document_url, '/')) : null;
                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'message_text' => $msg->message_text,
                    'media_url' => $mediaUrl,
                    'media_type' => $msg->media_type,
                    'thumbnail' => $msg->thumbnail,
                    'duration' => $msg->duration,
                    'file_size' => $msg->file_size,
                    'group_id' => $msg->group_id,
                    'status' => $msg->status,
                    'read_at' => $msg->read_at,
                    'document_url' => $documentUrl,
                    'link_url' => $msg->link_url,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                ];
            });

            $result = [
                'user' => $otherUserProfile,
                'data' => $processedMessages,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'has_more' => $page < $lastPage,
            ];

            return [
                'data' => $result,
                'message' => __('message.chat_history_retrieved_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to retrieve chat history: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_retrieve_chat_history'));
        }
    }

    public function deleteMessage($userId, $messageId)
    {
        try {
            $message = $this->messageRepo->find($messageId);
            if (!$message) {
                throw new Exception(__('message.message_not_found'));
            }
            if ($message->sender_id != $userId) {
                throw new Exception(__('message.delete_only_own_message'));
            }
            $deleted = $this->messageRepo->deleteData(['id' => $messageId]);
            if (!$deleted) {
                throw new Exception(__('message.failed_to_delete_message'));
            }
            return [
                'data' => [],
                'message' => __('message.message_deleted_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to delete message: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_delete_message'));
        }
    }

    // Refactored to accept $currentUserId and match FriendshipService
    private function processUserData($user, $currentUserId)
    {
        $age = $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->age : null;

        $images = [];
        if ($user->images) {
            $userImages = is_string($user->images) ? json_decode($user->images, true) : $user->images;
            if (is_array($userImages)) {
                $images = array_map(function ($imagePath) {
                    return asset('storage/' . $imagePath);
                }, $userImages);
            }
        }

        // You can add more fields as needed, similar to FriendshipService
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
            'last_seen_at' => $user->last_seen_at, // already formatted by accessor
        ];
    }

    public function getSentMessageUsers($userId, $request)
    {
        try {
            $perPage = $request['per_page'] ?? 20;
            $page = $request['page'] ?? 1;

            // Fetch messages where user is sender
            $sentMessages = $this->messageRepo->getByWhere(
                [
                    ['sender_id', '=', $userId],
                    ['receiver_id', '!=', null]
                ],
                ['created_at' => 'desc'],
                ['*'],
                ['receiver'],
                [],
                'get'
            );

            // Fetch messages where user is receiver
            $receivedMessages = $this->messageRepo->getByWhere(
                [
                    ['receiver_id', '=', $userId],
                    ['sender_id', '!=', null]
                ],
                ['created_at' => 'desc'],
                ['*'],
                ['sender'],
                [],
                'get'
            );

            // Collect unique users from both sent and received messages
            $uniqueUsers = [];
            $latestMessageTimes = [];

            foreach ($sentMessages as $msg) {
                if ($msg->receiver && $msg->receiver->id != $userId) {
                    $rid = $msg->receiver->id;
                    $latest = $msg->created_at;
                    if (!isset($latestMessageTimes[$rid]) || $latest > $latestMessageTimes[$rid]) {
                        $uniqueUsers[$rid] = $msg->receiver;
                        $latestMessageTimes[$rid] = $latest;
                    }
                }
            }
            foreach ($receivedMessages as $msg) {
                if ($msg->sender && $msg->sender->id != $userId) {
                    $sid = $msg->sender->id;
                    $latest = $msg->created_at;
                    if (!isset($latestMessageTimes[$sid]) || $latest > $latestMessageTimes[$sid]) {
                        $uniqueUsers[$sid] = $msg->sender;
                        $latestMessageTimes[$sid] = $latest;
                    }
                }
            }

            // Sort users by latest message time DESC (newest first)
            uasort($uniqueUsers, function ($a, $b) use ($latestMessageTimes) {
                return strtotime($latestMessageTimes[$b->id]) <=> strtotime($latestMessageTimes[$a->id]);
            });
            $uniqueUsers = array_values($uniqueUsers);

            // Pagination for unique users
            $total = count($uniqueUsers);
            $lastPage = (int) ceil($total / $perPage);
            $offset = ($page - 1) * $perPage;
            $paginatedUsers = array_slice($uniqueUsers, $offset, $perPage);

            // Prepare result with required parameters
            $usersWithDetails = [];
            foreach ($paginatedUsers as $otherUser) {
                
                // Unread message count (messages sent by this user to current user, unread)
                $unreadCount = $this->messageRepo->getByWhere(
                    [
                        ['sender_id', '=', $otherUser->id],
                        ['receiver_id', '=', $userId],
                        ['read_at', '=', null]
                    ],
                    [],
                    ['*'],
                    [],
                    [],
                    'count'
                );

                // Last message (both directions)
                $lastMsg = $this->messageRepo->getByWhere(
                    [
                        ['sender_id', '=', $userId],
                        ['receiver_id', '=', $otherUser->id]
                    ],
                    ['created_at' => 'desc'],
                    ['*'],
                    [],
                    [],
                    'first'
                );
                $lastMsgReverse = $this->messageRepo->getByWhere(
                    [
                        ['sender_id', '=', $otherUser->id],
                        ['receiver_id', '=', $userId]
                    ],
                    ['created_at' => 'desc'],
                    ['*'],
                    [],
                    [],
                    'first'
                );

                // Pick the latest message
                $lastMessage = null;
                if ($lastMsg && $lastMsgReverse) {
                    $lastMessage = ($lastMsg->created_at > $lastMsgReverse->created_at) ? $lastMsg : $lastMsgReverse;
                } elseif ($lastMsg) {
                    $lastMessage = $lastMsg;
                } elseif ($lastMsgReverse) {
                    $lastMessage = $lastMsgReverse;
                }

                // Prepare last message fields
                $last_message_at = $lastMessage ? $lastMessage->created_at : null;
                $last_message = $lastMessage ? $lastMessage->message_text : null;
                $last_message_type = $lastMessage ? $lastMessage->media_type : null;
                $last_message_status = $lastMessage ? $lastMessage->status : null;

                // Prepare image (first image or null)
                $images = [];
                if ($otherUser->images) {
                    $userImages = is_string($otherUser->images) ? json_decode($otherUser->images, true) : $otherUser->images;
                    if (is_array($userImages) && count($userImages) > 0) {
                        $images = array_map(function ($imagePath) {
                            return asset('storage/' . $imagePath);
                        }, $userImages);
                    }
                }
                $image = count($images) > 0 ? $images[0] : null;

                // Format last_seen_at if available
                $last_seen_at = null;
                if (!empty($otherUser->last_seen_at)) {
                    try {
                        $last_seen_at = \Carbon\Carbon::parse($otherUser->last_seen_at)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $last_seen_at = $otherUser->last_seen_at;
                    }
                }
                $blockData = Block::where(function ($q) use ($userId, $otherUser) {
                    $q->where('blocker_id', $userId)
                      ->where('blocked_id', $otherUser->id);
                })->orWhere(function ($q) use ($userId, $otherUser) {
                    $q->where('blocker_id', $otherUser->id)
                      ->where('blocked_id', $userId);
                })->first();
                if(isset($blockData->id)){
                    continue; // Skip this user if there is a block relationship
                }
                $usersWithDetails[] = [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'is_delete'=>$otherUser->is_delete,
                    'image' => $image,
                    'last_seen_at' => $last_seen_at,
                    'last_message_at' => $last_message_at,
                    'unread_message_count' => $unreadCount,
                    'last_message' => $last_message,
                    'last_message_type' => $last_message_type,
                    'last_message_status' => $last_message_status,
                ];
            }

            return [
                'data' => [
                    'users' => $usersWithDetails,
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total' => $total,
                        'last_page' => $lastPage,
                        'has_more' => $page < $lastPage
                    ],
                ],
                'message' => __('message.sent_message_users_fetched_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to fetch sent message users: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_fetch_sent_message_users'));
        }
    }

    public function createGroup($userId, $data)
    {
        try {
            // Validate required fields
            if (empty($data['name']) || empty($data['member_ids'])) {
                return [
                    'data' => null,
                    'message' => __('message.group_name_and_member_ids_required')
                ];
            }

            // Fix: handle member_ids as stringified array inside array
            // Example: ["[32, 38]"] => [32, 38]
            if (is_array($data['member_ids']) && count($data['member_ids']) === 1 && is_string($data['member_ids'][0]) && str_starts_with(trim($data['member_ids'][0]), '[')) {
                $decoded = json_decode($data['member_ids'][0], true);
                if (is_array($decoded)) {
                    $data['member_ids'] = $decoded;
                }
            } elseif (is_string($data['member_ids'])) {
                $data['member_ids'] = json_decode($data['member_ids'], true);
            }
            if (!is_array($data['member_ids'])) {
                $data['member_ids'] = [];
            }

            // Create group
            $group = $this->groupRepo->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'group_type' => $data['group_type'] ?? 'public',
                'is_member_permission' => $data['is_member_permission'] ?? true,
                'image' => $data['image'] ?? null,
                'created_by' => $userId,
            ]);

            // Add creator as a member (admin) with default permission true
            $this->groupRepo->addMemberToGroup([
                'group_id' => $group->id,
                'user_id' => $userId,
                'role' => 'admin',
                'is_member_permission' => true
            ]);
            // dd($data['member_ids']);
            // Add other members as 'member'
            foreach ($data['member_ids'] as $memberId) {
                if ($memberId != $userId) {
                    $this->groupRepo->addMemberToGroup([
                        'group_id' => $group->id,
                        'user_id' => $memberId,
                        'role' => 'member',
                        'is_member_permission' => true
                    ]);
                }
            }

            // Reload group with all members and their user relation
            $group = $this->groupRepo->model
                ->with(['members.user', 'creator'])
                ->find($group->id);

            // Format images for group and creator
            $group->image = getImageUrl($group->image);
            if ($group->creator) {
                $group->creator->images = getImagesArray($group->creator->images);
            }

            return [
                'data' => $group,
                'message' => __('message.group_created_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to create group: ' . $e->getMessage());
            throw new Exception(__('message.create_group_failed'));
        }
    }

    public function joinGroup($userId, $data)
    {
        try {
            // dd($userId);
            if (empty($data['group_id'])) {
                return [
                    'data' => null,
                    'message' => __('message.group_id_required')
                ];
            }
            $group = $this->groupRepo->find($data['group_id']);
            if (!$group) {
                return [
                    'data' => null,
                    'message' => __('message.group_not_found')
                ];
            }

            // Check if already a member
            if ($this->groupRepo->isMember($group->id, $userId)) {
                return [
                    'data' => null,
                    'message' => __('message.already_member_group')
                ];
            }

            // Check if user was removed (status=2)
            $wasRemoved = $this->groupRepo->isRemovedFromGroup($group->id, $userId);

            if ($group->group_type == 1 && $wasRemoved) {
               
                 // private group
                // Private: removed users cannot re-join or see group
                return [
                    'data' => null,
                    'message' => __('message.cannot_rejoin_private_group')
                ];
            }
            // dd('sdfkjsdfjsdf');

            if ($group->group_type === 0) {
                // Public: allow re-join
                $this->groupRepo->addMemberToGroup([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'role' => 'member'
                ]);
                return [
                    'data' => null,
                    'message' => __('message.joined_group_successfully')
                ];
            } else {
                // Private group: create join request (if not removed)
                $this->groupRepo->createJoinRequest($group->id, $userId);
                return [
                    'data' => null,
                    'message' => __('message.join_request_sent')
                ];
            }
        } catch (Exception $e) {
            \Log::error('Failed to join group: ' . $e->getMessage());
            throw new Exception(__('message.join_group_failed'));
        }
    }

    public function handleJoinRequest($adminId, $data)
    {
        try {
            if (empty($data['group_id']) || empty($data['user_id']) || empty($data['action'])) {
                return [
                    'data' => null,
                    'message' => __('message.group_user_action_required')
                ];
            }
            $group = $this->groupRepo->find($data['group_id']);
           
            if (!$group) {
                return [
                    'data' => null,
                    'message' => __('message.group_not_found')
                ];
            }
            // Only admin can accept/reject
            if (!$this->groupRepo->isAdmin($group->id, $adminId)) {
                return [
                    'data' => null,
                    'message' => __('message.only_admin_handle_requests')
                ];
            }
            if ($data['action'] === 'accept') {
                // Update group_status to 'accept' for the existing group_member entry
                $groupMember = $this->groupRepo->groupMemberModel
                    ->where('group_id', $group->id)
                    ->where('user_id', $data['user_id'])
                    ->first();
                if ($groupMember) {
                    $groupMember->group_status = 'accept';
                    $groupMember->is_member_permission = true;
                    $groupMember->status = 0; // make sure status is active
                    $groupMember->save();
                }
                // $this->groupRepo->deleteJoinRequest($group->id, $data['user_id']); // not needed
                return [
                    'data' => $groupMember,
                    'message' => __('message.join_request_accepted')
                ];
            } elseif ($data['action'] === 'reject') {
                
                $this->groupRepo->deleteJoinRequest($group->id, $data['user_id']);
                return [
                    'data' => null,
                    'message' => __('message.join_request_rejected')
                ];
            } else {
                return [
                    'data' => null,
                    'message' => __('message.invalid_action')
                ];
            }
        } catch (Exception $e) {
            \Log::error('Failed to handle join request: ' . $e->getMessage());
            throw new Exception(__('message.handle_join_request_failed'));
        }
    }

    public function deleteAllConversation($userId, $otherUserId)
    {
        try {
            // Delete all messages where user is sender and other is receiver, or vice versa
            $deleted1 = $this->messageRepo->getByWhere(
                [
                    ['sender_id', '=', $userId],
                    ['receiver_id', '=', $otherUserId]
                ],
                [],
                ['*'],
                [],
                [],
                'get'
            )->each(function ($msg) {
                $msg->delete();
            });

            $deleted2 = $this->messageRepo->getByWhere(
                [
                    ['sender_id', '=', $otherUserId],
                    ['receiver_id', '=', $userId]
                ],
                [],
                ['*'],
                [],
                [],
                'get'
            )->each(function ($msg) {
                $msg->delete();
            });

            return [
                'data' => [],
                'message' => __('message.conversation_deleted_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to delete all conversation messages: ' . $e->getMessage());
            throw new Exception(__('message.delete_all_conversation_failed'));
        }
    }


    public function getGroups($userId, $request)
    {
        try {
            $perPage = $request['per_page'] ?? 20;
            $page = $request['page'] ?? 1;

            // Only include group_ids where status != 2 (not left)
            $memberGroupIds = $this->groupRepo->groupMemberModel
                ->where('user_id', $userId)
                ->where('status', '!=', 2)
                ->where(function ($q) {
                    $q->whereNull('group_status')           // keep NULL rows
                      ->orWhere('group_status', '<>', 'pending'); // remove only pending
                })
                ->pluck('group_id')
                ->toArray();
            $groups = $this->groupRepo->model
                ->where(function ($query) use ($userId, $memberGroupIds) {
                    $query->where('created_by', $userId);
                    if (!empty($memberGroupIds)) {
                        $query->orWhereIn('id', $memberGroupIds);
                    }
                })
                ->with(['creator'])
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            foreach ($groups as $group) {
                $group->image = getImageUrl($group->image);
                if ($group->creator) {
                    $group->creator->images = getImagesArray($group->creator->images);
                }
                

                // Last message in group
                $lastMessage = $this->messageRepo->model
                    ->where('group_id', $group->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                    //   dd($lastMessage);
                $group->sender_id = $lastMessage ? $lastMessage->sender_id : null;
                $group->last_message = $lastMessage ? $lastMessage->message_text : null;
                $group->last_message_time = $lastMessage ? $lastMessage->created_at : null;
                $group->media_type = $lastMessage ? $lastMessage->media_type : null;
                
                // Unread message count for this user in this group
                $group->unread_count = $this->messageRepo->model
                    ->where('group_id', $group->id)
                    ->where('receiver_id', $userId)
                    ->whereNull('read_at')
                    ->count();
            }

            return [
                'data' => $groups,
                'message' => __('message.groups_retrieved_successfully')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to retrieve groups: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_retrieve_groups'));
        }
    }

    public function isGroupNameExists($name)
    {
        try {
            $exists = $this->groupRepo->isGroupNameExists($name);
            return [
                'data' => ['exists' => $exists],
                'message' => __('message.group_name_checked')
            ];
        } catch (Exception $e) {
            \Log::error('Failed to check group name existence: ' . $e->getMessage());
            throw new Exception(__('message.failed_to_check_group_name_existence'));
        }
    }

    public function searchGroups($keyword = '', $perPage = 15, $page = 1)
    {
        try {
            $userId = auth()->id(); // get logged-in user id for unread count
            $groups = $this->groupRepo->searchGroups($keyword, $perPage, $page);
            // Filter: For removed users (status=2), allow public groups, exclude private groups
            if ($userId) {
                $removedGroupIds = $this->groupRepo->groupMemberModel
                    ->where('user_id', $userId)
                    ->where('status', 2)
                    ->pluck('group_id')
                    ->toArray();

                if (!empty($removedGroupIds)) {
                    foreach ($groups as $key => $group) {
                        if (
                            in_array($group->id, $removedGroupIds)
                            && $group->group_type == 1 // private
                        ) {
                            unset($groups[$key]);
                        }
                        // For public, do NOT unset
                    }
                    // Re-index after unset
                    $groups = $groups instanceof \Illuminate\Pagination\LengthAwarePaginator
                        ? $groups->setCollection($groups->getCollection()->values())
                        : collect($groups)->values();
                }
            }

            foreach ($groups as $group) {
                $group->image = getImageUrl($group->image);
                if ($group->creator) {
                    $group->creator->images = getImagesArray($group->creator->images);
                }

                // Last message in group
                $lastMessage = $this->messageRepo->model
                    ->where('group_id', $group->id)
                    ->orderBy('created_at', 'desc')
                    ->first();
                  
                $group->sender_id = $lastMessage ? $lastMessage->sender_id : null;
                $group->last_message = $lastMessage ? $lastMessage->message_text : null;
                $group->last_message_time = $lastMessage ? $lastMessage->created_at : null;
                $group->media_type = $lastMessage ? $lastMessage->media_type : null;
                // Unread message count for this user in this group
                $group->unread_count = $userId
                    ? $this->messageRepo->model
                    ->where('group_id', $group->id)
                    ->where('receiver_id', $userId)
                    ->whereNull('read_at')
                    ->count()
                    : 0;
            }
            return [
                'data' => $groups,
                'message' => __('message.groups_fetched_successfully')
            ];
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            throw new Exception(__('message.search_groups_failed'));
        }
    }

    /**
     * Add a member to a group (admin only).
     */
    public function addMemberToGroup($adminId, $data)
    {
        try {
           
            if (empty($data['group_id']) || empty($data['user_ids']) || !is_array($data['user_ids'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_and_user_ids_required'),
                    'code' => 422,
                ];
            }

            $groupId = $data['group_id'];
            $userIds = $data['user_ids'];
            $role = $data['role'] ?? 'member';

            // Only group admin can add members
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_add_members'),
                    'code' => 403,
                ];
            }

            $added = [];
            $skipped = [];
          
            foreach ($userIds as $memberId) {
                if ($this->groupRepo->isMember($groupId, $memberId)) {
                    $skipped[] = $memberId;
                    continue;
                }
              
                
                $result = $this->groupRepo->addMemberToGroup([
                    'group_id' => $groupId,
                    'user_id' => $memberId,
                    'role' => $role
                ]);
                if ($result) {
                    $added[] = $memberId;
                }
            }

            if (empty($added)) {
                return [
                    'error' => true,
                    'message' => __('message.no_new_members_added'),
                    'code' => 409,
                ];
            }

            return [
                'error' => false,
                'data' => [
                    'added' => $added,
                    'skipped' => $skipped
                ],
                'message' => __('message.members_added_successfully'),
            ];
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_add_members'),
                'code' => 500,
            ];
        }
    }

    /**
     * Remove a member from a group (admin only).
     */
    public function removeMemberFromGroup($adminId, $data)
    {
        try {
            if (empty($data['group_id']) || empty($data['user_id'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_and_user_id_required'),
                    'code' => 422,
                ];
            }

            $groupId = $data['group_id'];
            $memberId = $data['user_id'];

            // Only group admin can remove members
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_remove_members'),
                    'code' => 403,
                ];
            }

            // Check if user is a member
            if (!$this->groupRepo->isMember($groupId, $memberId)) {
                return [
                    'error' => true,
                    'message' => __('message.user_not_member_of_group'),
                    'code' => 404,
                ];
            }

            // Prevent admin from removing themselves (optional, can remove if needed)
            if ($adminId == $memberId) {
                return [
                    'error' => true,
                    'message' => __('message.admin_cannot_remove_self'),
                    'code' => 403,
                ];
            }

            $deleted = $this->groupRepo->removeMemberFromGroup($groupId, $memberId);

            if (!$deleted) {
                return [
                    'error' => true,
                    'message' => __('message.failed_to_remove_member_from_group'),
                    'code' => 500,
                ];
            }

            return [
                'error' => false,
                'data' => ['member_id' => $memberId],
                'message' => __('message.member_removed_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_remove_member_from_group'),
                'code' => 500,
            ];
        }
    }

    /**
     * Block or unblock a group member (admin only).
     * $status: 1 = block, 0 = unblock
     */
    public function blockOrUnblockGroupMember($adminId, $data, $status)
    {
        try {
            if (empty($data['group_id']) || empty($data['user_id'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_and_user_id_required'),
                    'code' => 422,
                ];
            }
            $groupId = $data['group_id'];
            $memberId = $data['user_id'];
            // Only group admin can block/unblock
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_perform_action'),
                    'code' => 403,
                ];
            }

            // Check if user is a member
            if (!$this->groupRepo->isMember($groupId, $memberId)) {
                return [
                    'error' => true,
                    'message' => __('message.user_not_member_of_group'),
                    'code' => 404,
                ];
            }

            // Prevent admin from blocking/unblocking themselves
            if ($adminId == $memberId) {
                return [
                    'error' => true,
                    'message' => __('message.admin_cannot_block_self'),
                    'code' => 403,
                ];
            }
            $updated = $this->groupRepo->updateGroupMemberStatus($groupId, $memberId, $status);
            if($status==0){
                $this->friendshipRepo->friendDelete(['user_id' => $adminId, 'friend_id' => $memberId]);
                $this->groupRepo->delete(['group_id' => $groupId, 'user_id' => $memberId]);
            }
            if (!$updated) {
                
                return [
                    'error' => true,
                    'message' => ($status == 1 ? 'Failed to block' : 'Failed to unblock') . ' group member.',
                    'code' => 500,
                ];
            }

            // --- GLOBAL BLOCK/UNBLOCK LOGIC ---
            if ($status == 1) {
                // Block globally using FriendshipRepository
                if ($this->friendshipRepo && !$this->friendshipRepo->isBlocked($adminId, $memberId)) {
                    $this->friendshipRepo->createBlock([
                        'blocker_id' => $adminId,
                        'blocked_id' => $memberId
                    ]);
                }
            } else {
                // Unblock globally using FriendshipRepository
                if ($this->friendshipRepo) {
                    $block = $this->friendshipRepo->findBlockByUsers($adminId, $memberId);
                    if ($block) {
                        $this->friendshipRepo->deleteBlock($block->id);
                    }
                }
            }
            // --- END GLOBAL BLOCK/UNBLOCK LOGIC ---

            return [
                'error' => false,
                'data' => ['member_id' => $memberId, 'status' => $status],
                'message' => $status == 1 ? __('message.member_blocked_successfully') : __('message.member_unblocked_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_update_group_member_status'),
                'code' => 500,
            ];
        }
    }

    /**
     * Update permission for all members in a group (admin only).
     */
    public function updateGroupPermissionForAll($adminId, $data)
    {
        try {
            if (empty($data['group_id']) || !isset($data['is_member_permission'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_and_permission_required'),
                    'code' => 422,
                ];
            }
            $groupId = $data['group_id'];
            $isMemberPermission = (bool)$data['is_member_permission'];

            // Only group admin can update
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_update_permissions'),
                    'code' => 403,
                ];
            }

            $updated = $this->groupRepo->updateAllMembersPermission($groupId, $isMemberPermission);

            if ($updated === false) {
                return [
                    'error' => true,
                    'message' => __('message.failed_to_update_permissions_for_all'),
                    'code' => 500,
                ];
            }

            // Also update group table if needed
            $this->groupRepo->model->where('id', $groupId)->update(['is_member_permission' => $isMemberPermission]);

            return [
                'error' => false,
                'data' => ['group_id' => $groupId, 'is_member_permission' => $isMemberPermission],
                'message' => __('message.group_permissions_updated'),
            ];
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_update_permissions_for_all'),
                'code' => 500,
            ];
        }
    }

    /**
     * Update permission for a particular member in a group (admin only).
     */
    public function updateGroupPermissionForMember($adminId, $data)
    {
        try {
            if (empty($data['group_id']) || empty($data['user_id']) || !isset($data['is_member_permission'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_user_permission_required'),
                    'code' => 422,
                ];
            }
            $groupId = $data['group_id'];
            $userId = $data['user_id'];
            $isMemberPermission = (bool)$data['is_member_permission'];

            // Only group admin can update
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_update_permissions'),
                    'code' => 403,
                ];
            }

            // Check if user is a member
            if (!$this->groupRepo->isMember($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.user_not_member_of_group'),
                    'code' => 404,
                ];
            }

            $updated = $this->groupRepo->updateMemberPermission($groupId, $userId, $isMemberPermission);

            if ($updated === false) {
                return [
                    'error' => true,
                    'message' => __('message.failed_to_update_member_permission'),
                    'code' => 500,
                ];
            }

            return [
                'error' => false,
                'data' => ['group_id' => $groupId, 'user_id' => $userId, 'is_member_permission' => $isMemberPermission],
                'message' => __('message.member_permission_updated'),
            ];
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_update_member_permission'),
                'code' => 500,
            ];
        }
    }

    /**
     * Get a member's permission in a group.
     */
    public function getGroupMemberPermission($adminId, $data)
    {
        try {
            if (empty($data['group_id']) || empty($data['user_id'])) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_and_user_id_required'),
                    'code' => 422,
                ];
            }
            $groupId = $data['group_id'];
            $userId = $data['user_id'];

            // Only group admin can view
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_view_permissions'),
                    'code' => 403,
                ];
            }

            $permission = $this->groupRepo->getMemberPermission($groupId, $userId);

            return [
                'error' => false,
                'data' => ['group_id' => $groupId, 'user_id' => $userId, 'is_member_permission' => $permission],
                'message' => __('message.member_permission_fetched')
            ];
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_fetch_member_permission'),
                'code' => 500,
            ];
        }
    }

    /**
     * Get all blocked members of a group (status = 1).
     */
    public function getBlockedGroupMembers($adminId, $groupId)
    {
        // Only admin can view blocked members
        if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
            return [];
        }
        $blocked = $this->groupRepo->groupMemberModel
            ->with('user')
            ->where('group_id', $groupId)
            ->where('status', 1)
            ->get();

        $result = [];
        foreach ($blocked as $member) {
            $user = $member->user;
            $result[] = [
                'id' => $user ? $user->id : null,
                'name' => $user ? $user->name : null,
                'images' => $user ? getImagesArray($user->images) : [],
                'role' => $member->role,
                'status' => $member->status,
                'is_member_permission' => $member->is_member_permission ?? true,
            ];
        }
        return $result;
    }

    /**
     * Get all messages, group info, and member list for a group.
     */
    public function getGroupConversationDetail($userId, $groupId, $request = [])
    {
        try {
            $group = $this->groupRepo->model
                ->with(['creator', 'members.user'])
                ->find($groupId);
            if (!$group) {
                return [
                    'error' => true,
                    'message' => __('message.group_not_found'),
                    'code' => 404,
                ];
            }

            // Check if user is removed from group (status=2)
            $groupMember = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();

            $wasRemoved = $groupMember && $groupMember->status == 2;
            $deletedAt = $groupMember && !empty($groupMember->group_deleted_at) ? $groupMember->group_deleted_at : null;

            // For private groups, removed users cannot view info
            if ($group->group_type == 1 && $wasRemoved) {
                return [
                    'error' => true,
                    'message' => __('message.no_access_private_group'),
                    'code' => 403,
                ];
            }

            // For public groups, allow removed users to view info/messages
            // For all others, check membership
            // if ($group->group_type == 0 && $wasRemoved) {
            //     // allow
            // } else if (!$this->groupRepo->isMember($groupId, $userId)) {
            //     return [
            //         'error' => true,
            //         'message' => __('message.you_are_not_member_of_group'),
            //         'code' => 403,
            //     ];
            // }

        
            $perPage = (int)($request['per_page'] ?? 20);
            $page = (int)($request['page'] ?? 1);

            // Get messages for this group, filter if deletedAt is set
            $messagesQuery = $this->messageRepo->model->with('sender:id,is_delete')
                ->where('group_id', $groupId)
                ->orderBy('created_at', 'desc');

            if ($deletedAt) {
                $messagesQuery->where('created_at', '>', $deletedAt);
            }

            $messages = $messagesQuery->paginate($perPage, ['*'], 'page', $page);
            $members = [];
            foreach ($group->members as $member) {
                if ($member->group_status === 'pending') {
                    continue; // skip pending members
                }
                $userObj = $member->user;
                if($userObj->is_delete==1){
                    continue;
                }
                $members[] = [
                    'id' => $userObj ? $userObj->id : null,
                    'name' => $userObj ? $userObj->name : null,
                    'images' => $userObj ? getImagesArray($userObj->images) : [],
                    'role' => $member->role,
                    'status' => $member->status,
                    'group_status' => $member->group_status,
                    'is_member_permission' => $member->is_member_permission ?? true,
                ];
            }

            // Format messages
            $messagesData = [];
            foreach ($messages as $msg) {
                if($msg->sender->is_delete){
                    continue;
                }
                // Determine media_url
                $mediaUrl = null;
                if ($msg->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->media_url, '/'));
                } elseif ($msg->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->document_url, '/'));
                } elseif ($msg->link_url) {
                    $mediaUrl = $msg->link_url;
                }
                $documentUrl = $msg->document_url ? asset('storage/' . ltrim($msg->document_url, '/')) : null;

                $messagesData[] = [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'message_text' => $msg->message_text,
                    'media_url' => $mediaUrl,
                    'media_type' => $msg->media_type,
                    'thumbnail' => $msg->thumbnail,
                    'duration' => $msg->duration,
                    'file_size' => $msg->file_size,
                    'status' => $msg->status,
                    'read_at' => $msg->read_at,
                    'group_status' => $msg->group_status,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                    'document_url' => $documentUrl,
                    'link_url' => $msg->link_url,
                ];
            }
            $request_user_list_raw = $this->groupRepo->getRequestedGroupsByUser($groupId);
            $request_user_list = [];
            foreach ($request_user_list_raw as $member) {
                $user = $member->user;
                if ($user) {
                    $request_user_list[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'images' => getImagesArray($user->images),
                        'role' => $member->role,
                        'status' => $member->status,
                        'group_status' => $member->group_status,
                        'is_member_permission' => $member->is_member_permission ?? true,
                    ];
                }
            }

            // Last message in group
            $lastMessage = $this->messageRepo->model
                ->where('group_id', $group->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $lastMessageArr = null;
            if ($lastMessage) {
                // Determine media_url for last message
                $mediaUrl = null;
                if ($lastMessage->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($lastMessage->media_url, '/'));
                } elseif ($lastMessage->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($lastMessage->document_url, '/'));
                } elseif ($lastMessage->link_url) {
                    $mediaUrl = $lastMessage->link_url;
                }
                $documentUrl = $lastMessage->document_url ? asset('storage/' . ltrim($lastMessage->document_url, '/')) : null;

                $lastMessageArr = [
                    'id' => $lastMessage->id,
                    'sender_id' => $lastMessage->sender_id,
                    'message_text' => $lastMessage->message_text,
                    'media_url' => $mediaUrl,
                    'media_type' => $lastMessage->media_type,
                    'thumbnail' => $lastMessage->thumbnail,
                    'duration' => $lastMessage->duration,
                    'file_size' => $lastMessage->file_size,
                    'status' => $lastMessage->status,
                    'read_at' => $lastMessage->read_at,
                    'group_status' => $lastMessage->group_status,
                    'created_at' => $lastMessage->created_at,
                    'updated_at' => $lastMessage->updated_at,
                    'document_url' => $documentUrl,
                    'link_url' => $lastMessage->link_url,
                ];
            }

            // Unread message count for this user in this group
            $unreadCount = $this->messageRepo->model
                ->where('group_id', $group->id)
                ->where('receiver_id', $userId)
                ->whereNull('read_at')
                ->count();

            // Format group info
            $groupInfo = [
                'group_id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'image' => getImageUrl($group->image),
                'group_type' => $group->group_type,
                'is_member_permission' => $group->is_member_permission,
                'created_by' => $group->created_by,
                'notification_status' => $group->notification_status,
                'creator' => $group->creator ? [
                    'id' => $group->creator->id,
                    'name' => $group->creator->name,
                    'images' => getImagesArray($group->creator->images),
                ] : null,
                'members' => $members,
                'request_user_list' => $request_user_list,
                'last_message' => $lastMessageArr,
                'unread_count' => $unreadCount,
            ];

            $result = [
                'group' => $groupInfo,
                'messages' => $messagesData,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'last_page' => $messages->lastPage(),
                    'has_more' => $messages->currentPage() < $messages->lastPage(),
                ],
            ];

            return [
                'error' => false,
                'data' => $result,
                'message' => __('message.group_messages_members_fetched')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getGroupConversationDetail: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_fetch_group_conversation_detail'),
                'code' => 500,
            ];
        }
    }

    /**
     * Delete a group (admin only).
     */
    public function deleteGroup($userId, $groupId)
    {
        try {
            $group = $this->groupRepo->model->find($groupId);
            if (!$group) {
                return [
                    'error' => true,
                    'message' => __('message.group_not_found'),
                    'code' => 404,
                ];
            }
            // Only admin can delete
            if (!$this->groupRepo->isAdmin($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_delete_group'),
                    'code' => 403,
                ];
            }
            // Delete all group members
            $this->groupRepo->groupMemberModel->where('group_id', $groupId)->delete();
            // Delete all group messages
            $this->messageRepo->model->where('group_id', $groupId)->delete();
            // Delete the group itself
            $group->delete();

            return [
                'error' => false,
                'message' => __('message.group_deleted_successfully')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in deleteGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_delete_group'),
                'code' => 500,
            ];
        }
    }

    /**
     * Get all group details: info, members, blocked users, media, etc.
     */
    public function getGroupDetails($userId, $groupId, $request = [])
    {
        try {
            $group = $this->groupRepo->model
                ->with(['creator', 'members.user'])
                ->find($groupId);
            if (!$group) {
                return [
                    'error' => true,
                    'message' => __('message.group_not_found'),
                    'code' => 404,
                ];
            }
            // Check if user is removed from group (status=2)
            $groupMember = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();
            $wasRemoved = $groupMember && $groupMember->status == 2;

            // For private groups, removed users cannot view info
            if ($group->group_type == 1 && $wasRemoved) {
                return [
                    'error' => true,
                    'message' => __('message.no_access_private_group'),
                    'code' => 403,
                ];
            }

            // Members: Exclude pending members
            $members = [];
            foreach ($group->members as $member) {
                if ($member->group_status === 'pending') {
                    continue; // skip pending members
                }
                $userObj = $member->user;
                $members[] = [
                    'id' => $userObj ? $userObj->id : null,
                    'name' => $userObj ? $userObj->name : null,
                    'images' => $userObj ? getImagesArray($userObj->images) : [],
                    'role' => $member->role,
                    'status' => $member->status,
                    'is_member_permission' => $member->is_member_permission ?? true,
                    'group_status' => $member->group_status ?? null,
                ];
            }
            $request_user_list_raw = $this->groupRepo->getRequestedGroupsByUser($groupId);
            $request_user_list = [];
            foreach ($request_user_list_raw as $member) {
                $user = $member->user;
                if ($user) {
                    $request_user_list[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'images' => getImagesArray($user->images),
                        'role' => $member->role,
                        'status' => $member->status,
                        'group_status' => $member->group_status,
                        'is_member_permission' => $member->is_member_permission ?? true,
                    ];
                }
            }
            // Group info
            $groupInfo = [
                'group_id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'image' => getImageUrl($group->image),
                'group_type' => $group->group_type,
                'is_member_permission' => $group->is_member_permission,
                'created_by' => $group->created_by,
                'notification_status' => $group->notification_status,
                'creator' => $group->creator ? [
                    'id' => $group->creator->id,
                    'name' => $group->creator->name,
                    'images' => getImagesArray($group->creator->images),
                ] : null,
                'members' => $members,
                'request_user_list' => $request_user_list,
            ];

            // Blocked users
            $blocked = $this->groupRepo->groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->get();
            $blockedUsers = [];
            foreach ($blocked as $member) {
                $user = $member->user;
                $blockedUsers[] = [
                    'id' => $user ? $user->id : null,
                    'name' => $user ? $user->name : null,
                    'images' => $user ? getImagesArray($user->images) : [],
                    'role' => $member->role,
                    'status' => $member->status,
                    'is_member_permission' => $member->is_member_permission ?? true,
                ];
            }

      

            $result = [
                'group' => $groupInfo,
                'blocked_users' => $blockedUsers,
              
            ];

            return [
                'error' => false,
                'data' => $result,
                'message' => __('message.group_details_fetched')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getGroupDetails: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.fetch_group_details_failed'),
                'code' => 500,
            ];
        }
    }

    /**
     * Edit group info (only provided fields, admin only).
     */
    public function editGroup($userId, $data)
    {
        try {
            $groupId = $data['group_id'] ?? null;
            if (!$groupId) {
                return [
                    'error' => true,
                    'message' => __('message.group_id_required'),
                    'code' => 400,
                ];
            }
            $group = $this->groupRepo->model->find($groupId);
            if (!$group) {
                return [
                    'error' => true,
                    'message' => __('message.group_not_found'),
                    'code' => 404,
                ];
            }
            // Only admin can edit
            if (!$this->groupRepo->isAdmin($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_edit_group'),
                    'code' => 403,
                ];
            }

            // Only update allowed fields and only those provided
            $updatable = [
                'name',
                'description',
                'image',
                'group_type',
                'is_member_permission',
                'notification_status',
            ];
            $updateData = [];
            foreach ($updatable as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (empty($updateData)) {
                return [
                    'error' => true,
                    'message' => __('message.no_valid_fields_for_update'),
                    'code' => 400,
                ];
            }

            // Validate non-nullable fields before update
            $nonNullable = ['group_type'];
            foreach ($nonNullable as $field) {
                if (array_key_exists($field, $updateData) && (is_null($updateData[$field]) || $updateData[$field] === '')) {
                    return [
                        'error' => true,
                        'message' => __('message.the_field_field_cannot_be_null_or_empty'),
                        'code' => 422,
                    ];
                }
            }

            try {
                $group->update($updateData);
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle SQL errors, especially integrity constraint violations
                if ($e->getCode() == 23000) {
                    return [
                        'error' => true,
                        'message' => __('message.invalid_group_field_value'),
                        'code' => 422,
                    ];
                }
                \Log::error('Error in editGroup (DB): ' . $e->getMessage());
                return [
                    'error' => true,
                    'message' => __('message.database_error_updating_group'),
                    'code' => 500,
                ];
            }

            // Reload group with members and creator
            $group = $this->groupRepo->model
                ->with(['members.user', 'creator'])
                ->find($groupId);

            // Format images for group and creator
            $group->image = getImageUrl($group->image);
            if ($group->creator) {
                $group->creator->images = getImagesArray($group->creator->images);
            }


            $members = [];
            foreach ($group->members as $member) {
                $userObj = $member->user;
                $members[] = [
                    'id' => $userObj ? $userObj->id : null,
                    'name' => $userObj ? $userObj->name : null,
                    'images' => $userObj ? getImagesArray($userObj->images) : [],
                    'role' => $member->role,
                    'status' => $member->status,
                    'is_member_permission' => $member->is_member_permission ?? true,
                    'group_status' => $member->group_status ?? null,
                ];
            }
            $request_user_list_raw = $this->groupRepo->getRequestedGroupsByUser($groupId);
            $request_user_list = [];
            foreach ($request_user_list_raw as $member) {
                $user = $member->user;
                if ($user) {
                    $request_user_list[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'images' => getImagesArray($user->images),
                        'role' => $member->role,
                        'status' => $member->status,
                        'group_status' => $member->group_status,
                        'is_member_permission' => $member->is_member_permission ?? true,
                    ];
                }
            }
            // Format group info
            $groupInfo = [
                'group_id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'image' => getImageUrl($group->image),
                'group_type' => $group->group_type,
                'is_member_permission' => $group->is_member_permission,
                'created_by' => $group->created_by,
                'notification_status' => $group->notification_status,
                'creator' => $group->creator ? [
                    'id' => $group->creator->id,
                    'name' => $group->creator->name,
                    'images' => getImagesArray($group->creator->images),
                ] : null,

                'members' => $members,
                'request_user_list' => $request_user_list,
            ];

        
            $result = [
               
                'group' => $groupInfo,
               
            ];

            return [
                'error' => false,
                'data' => $result,
                'message' => __('message.group_updated_successfully')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in editGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_update_group'),
                'code' => 500,
            ];
        }
    }

    /**
     * Leave group (member removes themselves).
     */
    public function leaveGroup($userId, $groupId)
    {
        try {
            $group = $this->groupRepo->model->find($groupId);
            if (!$group) {
                return [
                    'error' => true,
                    'message' => __('message.group_not_found'),
                    'code' => 404,
                ];
            }
            // Check if user is a member
            if (!$this->groupRepo->isMember($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.you_are_not_member_of_group'),
                    'code' => 403,
                ];
            }
            // Prevent admin from leaving (optional: you can allow if you want)
            if ($this->groupRepo->isAdmin($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.admin_cannot_leave_group'),
                    'code' => 403,
                ];
            }
            $deleted = $this->groupRepo->removeMemberFromGroup($groupId, $userId);
            if (!$deleted) {
                return [
                    'error' => true,
                    'message' => __('message.failed_to_leave_group'),
                    'code' => 500,
                ];
            }
            return [
                'error' => false,
                'message' => __('message.left_group_successfully')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in leaveGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_leave_group'),
                'code' => 500,
            ];
        }
    }

    /**
     * Report a group for abuse/inappropriate content.
     */
    public function reportGroup($userId, $data)
    {
        try {
            $groupId = $data['group_id'] ?? null;
            $reason = $data['reason'] ?? null;
            $email = $data['email'] ?? null;
            $reportType = $data['report_type'] ?? null;
            $image = $data['image'] ?? null;

            if (!$groupId || !$reason || !$reportType) {
                return [
                    'error' => true,
                    'message' => __('message.group_reason_report_type_required'),
                    'code' => 422,
                ];
            }

            // Use repository to check for duplicate report
            if ($this->groupRepo->hasReportedGroup($groupId, $userId)) {
                return [
                    'error' => true,
                    'message' => __('message.already_reported_group'),
                    'code' => 409,
                ];
            }

            // Prepare report data
            $reportData = [
                'group_id' => $groupId,
                'reported_by' => $userId,
                'reason' => $reason,
                'report_type' => $reportType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($email) {
                $reportData['email'] = $email;
            }
            if ($image) {
                $reportData['image'] = $image;
            }

            // Use repository to store the report
            $report = $this->groupRepo->groupReportModel->create($reportData);

            // Format image URL if present
            $reportArr = $report ? $report->toArray() : [];
            if (!empty($reportArr['image'])) {
                $reportArr['image'] = asset('storage/' . ltrim($reportArr['image'], '/'));
            }

            return [
                'error' => false,
                'data' => $reportArr,
                'message' => __('message.group_reported_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_report_group'),
                'code' => 500,
            ];
        }
    }
    
    public function userGroup($userId, $data)
    {
        try {
            $toUserId = $data['user_id'] ?? null;
            $reason = $data['reason'] ?? null;
            $email = $data['email'] ?? null;
            $reportType = $data['report_type'] ?? null;
            $image = $data['image'] ?? null;

            if (!$toUserId || !$reason || !$reportType) {
                return [
                    'error' => true,
                    'message' => __('message.user_reason_report_type_required'),
                    'code' => 422,
                ];
            }
            $checkExistingReport=$this->groupRepo->whereData(['user_id'=>$toUserId,'reported_by'=>$userId])->exists();
            // Use repository to check for duplicate report
            if ($checkExistingReport) {
                return [
                    'error' => true,
                    'message' => __('message.already_reported_user'),
                    'code' => 409,
                ];
            }

            // Prepare report data
            $reportData = [
                'user_id'=>$toUserId,
                'reported_by' => $userId,
                'reason' => $reason,
                'report_type' => $reportType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($email) {
                $reportData['email'] = $email;
            }
            if ($image) {
                $reportData['image'] = $image;
            }

            // Use repository to store the report
            $report = $this->groupRepo->groupReportModel->create($reportData);

            // Format image URL if present
            $reportArr = $report ? $report->toArray() : [];
            if (!empty($reportArr['image'])) {
                $reportArr['image'] = asset('storage/' . ltrim($reportArr['image'], '/'));
            }

            return [
                'error' => false,
                'data' => $reportArr,
                'message' => __('message.user_reported_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_report_group'),
                'code' => 500,
            ];
        }
    }
    
    public function pinGroup($userId,$data)
    {
        try {
            $toPinId = $data['pin_id'] ?? null;
            $reason = $data['reason'] ?? null;
            $email = $data['email'] ?? null;
            $reportType = $data['report_type'] ?? null;
            $image = $data['image'] ?? null;

            if (!$toPinId) {
                return [
                    'error' => true,
                    'message' => __('message.pin_id_required'),
                    'code' => 422,
                ];
            }
            $checkExistingReport=$this->groupRepo->whereData(['pin'=>$toPinId,'reported_by'=>$userId])->exists();
            // Use repository to check for duplicate report
            if ($checkExistingReport) {
                return [
                    'error' => true,
                    'message' => __('message.already_reported_pin'),
                    'code' => 409,
                ];
            }

            // Prepare report data
            $reportData = [
                'pin'=>$toPinId,
                'reported_by' => $userId,
                'reason' => $reason,
                'report_type' => $reportType,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            if ($email) {
                $reportData['email'] = $email;
            }
            if ($image) {
                $reportData['image'] = $image;
            }

            // Use repository to store the report
            $report = $this->groupRepo->groupReportModel->create($reportData);

            // Format image URL if present
            $reportArr = $report ? $report->toArray() : [];
            if (!empty($reportArr['image'])) {
                $reportArr['image'] = asset('storage/' . ltrim($reportArr['image'], '/'));
            }

            return [
                'error' => false,
                'data' => $reportArr,
                'message' => __('message.pin_reported_successfully'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => __('message.failed_to_report_pin'),
                'code' => 500,
            ];
        }
    }

    /**
     * Get paginated media, documents, or links for a group.
     * @param int $groupId
     * @param int $type 1=media, 2=document, 3=link
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function getGroupMediaList($groupId, $type, $perPage = 20, $page = 1)
    {
        try {
            $query = $this->messageRepo->model->where('group_id', $groupId)->with('sender:id,is_delete');

            if ($type == 1) {
                // Media (images/videos)
                $query->whereNotNull('media_url');
            } elseif ($type == 2) {
                // Documents
                $query->whereNotNull('document_url');
            } elseif ($type == 3) {
                // Links
                $query->whereNotNull('link_url');
            } else {
                return [
                    'data' => [],
                    'message' => __('message.invalid_type_parameter')
                ];
            }

            $query->orderBy('created_at', 'desc');
            
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
            $items = $paginator->getCollection()->filter(fn ($msg) => (int) optional($msg->sender)->is_delete !== 1)
                ->map(function ($msg) use ($type) {
                
                if ($type == 1) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->media_url ? asset('storage/' . ltrim($msg->media_url, '/')) : null,
                        'media_type' => $msg->media_type,
                        'thumbnail' => $msg->thumbnail,
                        'duration' => $msg->duration,
                        'file_size' => $msg->file_size,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                } elseif ($type == 2) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->document_url ? asset('storage/' . ltrim($msg->document_url, '/')) : null,
                        // 'original_name' => $msg->original_name ?? null,
                        'media_type' => $msg->media_type,
                        'mime_type' => $msg->mime_type ?? null,
                        'file_size' => $msg->file_size,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                } elseif ($type == 3) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->link_url,
                        'media_type' => $msg->media_type,
                        'message_text' => $msg->message_text,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                }
                return [];
            })->values();

            $data = [
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'has_more' => $paginator->currentPage() < $paginator->lastPage(),
                ],
            ];

            $msg = __('message.media_list_fetched');
            if ($type == 2) $msg = __('message.document_list_fetched');
            if ($type == 3) $msg = __('message.link_list_fetched');

            return [
                'data' => $data,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getGroupMediaList: ' . $e->getMessage());
            return [
                'data' => [],
                'message' => __('message.failed_to_fetch_group_media_list')
            ];
        }
    }

    /**
     * Get the latest message in a group chat.
     */
    public function getLatestGroupMessage($userId, $groupId, $limit = 20, $createdAt = null)
    {
        try {
            $query = $this->messageRepo->model
                ->where('group_id', $groupId)
                ->orderBy('created_at', 'desc');

            if ($createdAt) {
                // Accept both "2025-11-26 11:26:58" and "2025-11-26T11:26:58.000Z"
                $createdAtParsed = $createdAt;
                if (strpos($createdAt, 'T') !== false) {
                    $createdAtParsed = str_replace('T', ' ', $createdAt);
                    $createdAtParsed = preg_replace('/\.\d+Z$/', '', $createdAtParsed);
                }
                $query->where('created_at', '<', $createdAtParsed);
            }
            $messages = $query->limit($limit + 1)->get(); // fetch one extra for has_more

            $hasMore = $messages->count() > $limit;
            $data = $messages->take($limit)->map(function ($message) {
                $mediaUrl = null;
               
                if ($message->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($message->media_url, '/'));
                } elseif ($message->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($message->document_url, '/'));
                } elseif ($message->link_url) {
                    $mediaUrl = $message->link_url;
                }
                $documentUrl = $message->document_url ? asset('storage/' . ltrim($message->document_url, '/')) : null;
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'message_text' => $message->message_text,
                   
                    'media_url' => $mediaUrl,
                    'media_type' => $message->media_type,
                    'thumbnail' => $message->thumbnail,
                    'duration' => $message->duration,
                    'file_size' => $message->file_size,
                    'document_url' => $documentUrl,
                    'link_url' => $message->link_url,
                    'status' => $message->status,
                    'read_at' => $message->read_at,
                    'group_status' => $message->group_status,
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ];
            })->values();

            return [
                'data' => [
                    'messages' => $data,
                    'pagination' => [
                        'current_page' => $createdAt ? null : 1,
                        'per_page' => (int)$limit,
                        'has_more' => $hasMore,
                        'total' => null // not available in this cursor-based approach
                    ]
                ],
                'message' => __('message.latest_group_messages_fetched')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getLatestGroupMessage: ' . $e->getMessage());
            return [
                'data' => [
                    'messages' => [],
                    'pagination' => [
                        'current_page' => null,
                        'per_page' => (int)$limit,
                        'has_more' => false,
                        'total' => null
                    ]
                ],
                'message' => __('message.failed_to_fetch_latest_group_messages')
            ];
        }
    }

    public function getLatestIndividualMessage($userId, $otherUserId, $limit = 20, $createdAt = null)
    {
        try {
            $query = $this->messageRepo->model
                ->where(function ($q) use ($userId, $otherUserId) {
                    $q->where(function ($q2) use ($userId, $otherUserId) {
                        $q2->where('sender_id', $userId)
                            ->where('receiver_id', $otherUserId);
                    })->orWhere(function ($q2) use ($userId, $otherUserId) {
                        $q2->where('sender_id', $otherUserId)
                            ->where('receiver_id', $userId);
                    });
                })
                ->orderBy('created_at', 'desc');

            if ($createdAt) {
                // Accept both "2025-11-26 11:26:58" and "2025-11-26T11:26:58.000Z"
                $createdAtParsed = $createdAt;
                if (strpos($createdAt, 'T') !== false) {
                    $createdAtParsed = str_replace('T', ' ', $createdAt);
                    $createdAtParsed = preg_replace('/\.\d+Z$/', '', $createdAtParsed);
                }
                $query->where('created_at', '<', $createdAtParsed);
            }

            $messages = $query->limit($limit + 1)->get();

            $hasMore = $messages->count() > $limit;
            $data = $messages->take($limit)->map(function ($msg) {
                $mediaUrl = null;
                if ($msg->media_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->media_url, '/'));
                } elseif ($msg->document_url) {
                    $mediaUrl = asset('storage/' . ltrim($msg->document_url, '/'));
                } elseif ($msg->link_url) {
                    $mediaUrl = $msg->link_url;
                }
                $documentUrl = $msg->document_url ? asset('storage/' . ltrim($msg->document_url, '/')) : null;

                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'message_text' => $msg->message_text,
                    'media_url' => $mediaUrl,
                    'media_type' => $msg->media_type,
                    'thumbnail' => $msg->thumbnail,
                    'duration' => $msg->duration,
                    'file_size' => $msg->file_size,
                    'document_url' => $documentUrl,
                    'link_url' => $msg->link_url,
                    'status' => $msg->status,
                    'read_at' => $msg->read_at,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                ];
            })->values();

            return [
                'data' => [
                    'messages' => $data,
                    'pagination' => [
                        'current_page' => $createdAt ? null : 1,
                        'per_page' => (int)$limit,
                        'has_more' => $hasMore,
                        'total' => null
                    ]
                ],
                'message' => __('message.latest_individual_messages_fetched')
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getLatestIndividualMessage: ' . $e->getMessage());
            return [
                'data' => [
                    'messages' => [],
                    'pagination' => [
                        'current_page' => null,
                        'per_page' => (int)$limit,
                        'has_more' => false,
                        'total' => null
                    ]
                ],
                'message' => __('message.failed_to_fetch_latest_individual_messages')
            ];
        }
    }

    public function deleteAllGroupMessages($userId, $groupId)
    {
        try {
            // Mark the group chat as deleted for this user only (soft delete)
            // Assumes a 'group_members' table with a 'group_deleted_at' column (DATETIME, nullable)
            $groupMember = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();

            if ($groupMember) {
                $groupMember->group_deleted_at = now();
                $groupMember->save();
            }

            return [
                'data' => [],
                'message' => __('message.group_chat_deleted_for_user')
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to soft-delete group chat for user: ' . $e->getMessage());
            throw new \Exception(__('message.failed_to_delete_group_chat_for_user'));
        }
    }

    /**
     * Admin: Permanently delete all messages in a group for everyone.
     */
    public function deleteAllAdminGroupMessages($adminId, $groupId)
    {
        try {
            // Only admin can perform this action
            if (!$this->groupRepo->isAdmin($groupId, $adminId)) {
                return [
                    'error' => true,
                    'message' => __('message.only_admin_delete_all_group_messages'),
                    'code' => 403,
                ];
            }

            // Permanently delete all messages in this group
            $this->messageRepo->model
                ->where('group_id', $groupId)
                ->delete();

            return [
                'data' => [],
                'message' => __('message.group_messages_deleted_by_admin')
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to permanently delete all group messages by admin: ' . $e->getMessage());
            throw new \Exception(__('message.failed_to_delete_all_group_messages'));
        }
    }

    /**
     * Get paginated media, documents, or links for a 1-to-1 chat.
     * @param int $userId
     * @param int $otherUserId
     * @param int $type 1=media, 2=document, 3=link
     * @param int $perPage
     * @param int $page
     * @return array
     */
    public function getIndividualMediaList($userId, $otherUserId, $type, $perPage = 20, $page = 1)
    {
        try {
            $query = $this->messageRepo->model->where(function ($q) use ($userId, $otherUserId) {
                $q->where(function ($q2) use ($userId, $otherUserId) {
                    $q2->where('sender_id', $userId)
                        ->where('receiver_id', $otherUserId);
                })->orWhere(function ($q2) use ($userId, $otherUserId) {
                    $q2->where('sender_id', $otherUserId)
                        ->where('receiver_id', $userId);
                });
            });

            if ($type == 1) {
                $query->whereNotNull('media_url');
            } elseif ($type == 2) {
                $query->whereNotNull('document_url');
            } elseif ($type == 3) {
                $query->whereNotNull('link_url');
            } else {
                return [
                    'data' => [],
                    'message' => __('message.invalid_type_parameter')
                ];
            }

            $query->orderBy('created_at', 'desc');
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            $items = $paginator->getCollection()->map(function ($msg) use ($type) {
                if ($type == 1) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->media_url ? asset('storage/' . ltrim($msg->media_url, '/')) : null,
                        'media_type' => $msg->media_type,
                        'thumbnail' => $msg->thumbnail,
                        'duration' => $msg->duration,
                        'file_size' => $msg->file_size,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                } elseif ($type == 2) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->document_url ? asset('storage/' . ltrim($msg->document_url, '/')) : null,
                        // 'original_name' => $msg->original_name ?? null,
                        'media_type' => $msg->media_type,
                        'mime_type' => $msg->mime_type ?? null,
                        'file_size' => $msg->file_size,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                } elseif ($type == 3) {
                    return [
                        'id' => $msg->id,
                        'media_url' => $msg->link_url,
                        'media_type' => $msg->media_type,
                        'message_text' => $msg->message_text,
                        'created_at' => $msg->created_at,
                        'sender_id' => $msg->sender_id,
                    ];
                }
                return [];
            })->values();

            $data = [
                'items' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'has_more' => $paginator->currentPage() < $paginator->lastPage(),
                ],
            ];

            $msg = __('message.media_list_fetched');
            if ($type == 2) $msg = __('message.document_list_fetched');
            if ($type == 3) $msg = __('message.link_list_fetched');

            return [
                'data' => $data,
                'message' => $msg
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getIndividualMediaList: ' . $e->getMessage());
            return [
                'data' => [],
                'message' => __('message.failed_to_fetch_individual_media_list')
            ];
        }
    }

    /**
     * Get notification status for 1-to-1 chat.
     */
    public function getIndividualNotificationStatus($userId, $otherUserId)
    {
        return [
            'data' => [
                'notification_status' => $this->messageRepo->getNotificationStatus($userId, $otherUserId) ? 1 : 0
            ],
            'message' => __('message.notification_status_fetched')
        ];
    }

    /**
     * Set notification status for 1-to-1 chat.
     */
    public function setIndividualNotificationStatus($userId, $otherUserId, $status)
    {
        $ok = $this->messageRepo->setNotificationStatus($userId, $otherUserId, $status);
        return [
            'data' => [
                'notification_status' => $status ? 1 : 0
            ],
            'message' => $ok ? __('message.notification_status_updated') : __('message.no_chat_found_for_notification')
        ];
    }
}
