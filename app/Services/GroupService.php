<?php

namespace App\Services;

use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\MessageRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class GroupService
{
    protected GroupRepository $groupRepo;
    protected MessageRepository $messageRepo;

    public function __construct(GroupRepository $groupRepo, MessageRepository $messageRepo)
    {
        $this->groupRepo = $groupRepo;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Common success response.
     *
     * @param mixed $data
     * @param string $message
     * @return array
     */
    private function successResponse($data, string $message): array
    {
        return [
            'error' => false,
            'data' => $data,
            'message' => $message,
        ];
    }

    /**
     * Common error response.
     *
     * @param string $message
     * @param int $code
     * @return array
     */
    private function errorResponse(string $message, int $code = 400): array
    {
        return [
            'error' => true,
            'data' => null,
            'message' => $message,
            'code' => $code,
        ];
    }

    /**
     * Get group details API.
     *
     * @param int $userId
     * @param int $groupId
     * @return array
     */
    public function getGroupDetails(int $userId, int $groupId): array
    {
        try {

            $group = $this->groupRepo->getOneData(
                ['id' => $groupId],
                ['creator', 'members.user']
            );

            $accessCheck = $this->validateGroupAccess($group, $groupId, $userId);
            if (!empty($accessCheck['error'])) {
                return $accessCheck;
            }

            $subscriberCount = $this->groupRepo->groupMemberModel
            ->where('group_id', $groupId)
            ->whereNotIn('status', [1, 2])   
            ->where('is_delete', 0)
            ->count();

            $requestCount = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('group_status', 'pending')
                ->count();

            $userMember = $this->groupRepo->groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();

            $data = [
                'group' => [
                    'group_id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'image' => getImageUrl($group->image),
                    'group_type' => (int) $group->group_type,
                    'is_member_permission' => (int) $group->is_member_permission  == 1 ? true : false,
                    'created_by' => $group->created_by,
                    'subscriber_user_count' => $subscriberCount,
                    'user_request_count' => $requestCount,
                    'notification_status' => (int) ($group->notification_status ?? 0),
                    'user_detail' => ($userMember && $userMember->user)
                        ? $this->formatGroupMember($userMember)
                        : null,
                ],
            ];

            return $this->successResponse($data, __('message.group_details_fetched_successfully'));
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.fetch_group_details_failed'), 500);
        }
    }

    /**
     * Get group members with pagination.
     *
     * @param int $userId
     * @param int $groupId
     * @param array $request
     * @return array
     */
    public function getGroupMembers(int $userId, int $groupId, array $request = []): array
    {
        try {
            $group = $this->groupRepo->find($groupId);

            $accessCheck = $this->validateGroupAccess($group, $groupId, $userId);
            if (!empty($accessCheck['error'])) {
                return $accessCheck;
            }

            $perPage = max((int)($request['per_page'] ?? 20), 1);
            $page = max((int)($request['page'] ?? 1), 1);

            $members = $this->groupRepo->groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->where(function ($query) {
                    $query->whereNull('group_status')
                          ->orWhere('group_status', '!=', 'pending');
                })
                ->where('status', '==', 0)
                ->paginate($perPage, ['*'], 'page', $page);
          

            $memberData = [];
            foreach ($members->items() as $member) {
                if (!$member->user || (int)($member->user->is_delete ?? 0) === 1) {
                    continue;
                }

                $memberData[] = $this->formatGroupMember($member);
            }

            return $this->successResponse([
                'members' => $memberData,
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'per_page' => $members->perPage(),
                    'total' => $members->total(),
                    'last_page' => $members->lastPage(),
                    'has_more' => $members->currentPage() < $members->lastPage(),
                ],
            ], __('message.group_members_fetched_successfully'));
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.fetch_group_members_failed'), 500);
        }
    }

    /**
     * Get group join requests with pagination.
     *
     * @param int $userId
     * @param int $groupId
     * @param array $request
     * @return array
     */
    public function getGroupRequests(int $userId, int $groupId, array $request = []): array
    {
        try {
            $group = $this->groupRepo->find($groupId);

            $accessCheck = $this->validateGroupAccess($group, $groupId, $userId);
            if (!empty($accessCheck['error'])) {
                return $accessCheck;
            }

            $perPage = max((int)($request['per_page'] ?? 20), 1);
            $page = max((int)($request['page'] ?? 1), 1);

            $requests = $this->groupRepo->groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->where('group_status', 'pending')
                ->paginate($perPage, ['*'], 'page', $page);

            $requestUserList = [];
            foreach ($requests->items() as $member) {
                if (!$member->user || (int)($member->user->is_delete ?? 0) === 1) {
                    continue;
                }
              
                $requestUserList[] = $this->formatGroupMember($member);
            }

            return $this->successResponse([
                'request_user_list' => $requestUserList,
                'pagination' => [
                    'current_page' => $requests->currentPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
                    'last_page' => $requests->lastPage(),
                    'has_more' => $requests->currentPage() < $requests->lastPage(),
                ],
            ], __('message.group_requests_fetched_successfully'));
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.failed_to_fetch_group_requests'), 500);
        }
    }

    /**
     * Get group messages with pagination using repository getDataWithPagination().
     *
     * @param int $userId
     * @param int $groupId
     * @param array $request
     * @return array
     */
    public function getGroupMessages(int $userId, int $groupId, array $request = []): array
    {
        try {
            $group = $this->groupRepo->find($groupId);

            $accessCheck = $this->validateGroupAccess($group, $groupId, $userId);
            if (!empty($accessCheck['error'])) {
                return $accessCheck;
            }

            $groupMember = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();

            $deletedAt = $groupMember && !empty($groupMember->group_deleted_at)
                ? $groupMember->group_deleted_at
                : null;

            $perPage = max((int)($request['per_page'] ?? 20), 1);
            $page = max((int)($request['page'] ?? 1), 1);

            $whereConditions = [
                ['group_id', '=', $groupId],
            ];

            if ($deletedAt) {
                $whereConditions[] = ['created_at', '>', $deletedAt];
            }

            $messages = $this->messageRepo->getDataWithPagination(
                $whereConditions,
                ['sender:id,name,images,is_delete'],
                ['*'],
                [],
                ['created_at' => 'desc'],
                $perPage,
                $page
            );

            if (!$messages) {
                return $this->errorResponse(__('message.fetch_group_messages_failed'), 500);
            }

            $messagesData = [];
            foreach ($messages->items() as $msg) {
                if (!$msg->sender || (int)($msg->sender->is_delete ?? 0) === 1) {
                    continue;
                }
               
                $messagesData[] = $this->formatMessage($msg);
            }

            return $this->successResponse([
                'messages' => $messagesData,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'last_page' => $messages->lastPage(),
                    'has_more' => $messages->currentPage() < $messages->lastPage(),
                ],
            ], __('message.group_messages_fetched_successfully'));
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.fetch_group_messages_failed'), 500);
        }
    }

    /**
     * Get blocked users with pagination.
     *
     * @param int $authUserId
     * @param int $groupId
     * @param array $request
     * @return array
     */
    public function blockGroupUser(int $authUserId, int $groupId, array $request = []): array
    {
        try {
            $group = $this->groupRepo->find($groupId);

            $accessCheck = $this->validateGroupAccess($group, $groupId, $authUserId);
            if (!empty($accessCheck['error'])) {
                return $accessCheck;
            }

            $perPage = max((int)($request['per_page'] ?? 20), 1);
            $page = max((int)($request['page'] ?? 1), 1);

            $blockedUsers = $this->groupRepo->groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->where('status', 1)
                ->paginate($perPage, ['*'], 'page', $page);

            $members = [];
            foreach ($blockedUsers->items() as $member) {
                if (!$member->user) {
                    continue;
                }

                $members[] = $this->formatGroupMember($member);
            }

            return $this->successResponse([
                'members' => $members,
                'pagination' => [
                    'current_page' => $blockedUsers->currentPage(),
                    'per_page' => $blockedUsers->perPage(),
                    'total' => $blockedUsers->total(),
                    'last_page' => $blockedUsers->lastPage(),
                    'has_more' => $blockedUsers->currentPage() < $blockedUsers->lastPage(),
                ],
            ], __('message.blocked_users_fetched_successfully'));
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.failed_to_fetch_blocked_users'), 500);
        }
    }

    /**
     * Validate group access for current user.
     *
     * @param mixed $group
     * @param int $groupId
     * @param int $userId
     * @return array
     */
    private function validateGroupAccess($group, int $groupId, int $userId): array
    {
        try {
            if (!$group) {
                return $this->errorResponse(__('message.group_not_found'), 404);
            }

            $groupMember = $this->groupRepo->groupMemberModel
                ->where('group_id', $groupId)
                ->where('user_id', $userId)
                ->first();

            $wasRemoved = $groupMember && (int)$groupMember->status === 2;

            if ((int)$group->group_type === 1 && $wasRemoved) {
                return $this->errorResponse(__('message.no_access_to_private_group'), 403);
            }

            if ((int)$group->group_type === 1 && !$groupMember) {
                return $this->errorResponse(__('message.not_a_member_of_group'), 403);
            }

            return ['error' => false];
        } catch (Exception $e) {
            Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());
            return $this->errorResponse(__('message.failed_to_validate_group_access'), 500);
        }
    }

    /**
     * Format group member response.
     *
     * @param mixed $member
     * @return array
     */
    private function formatGroupMember($member): array
    {
        $user = $member->user;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'images' => getImagesArray($user->images),
            'role' => $member->role,
            'status' => $member->status,
            'group_status' => $member->group_status,
            'is_member_permission' => (int) $member->is_member_permission === 1 ? true : false,
            'is_delete' => $user->is_delete ?? 0,
        ];
    }

    
    /**
     * Format message response.
     *
     * @param mixed $msg
     * @return array
     */
    private function formatMessage($msg): array
    {
        $sender = $msg->sender;

        $mediaUrl = null;
        if (!empty($msg->media_url)) {
            $mediaUrl = asset('storage/' . ltrim($msg->media_url, '/'));
        } elseif (!empty($msg->document_url)) {
            $mediaUrl = asset('storage/' . ltrim($msg->document_url, '/'));
        } elseif (!empty($msg->link_url)) {
            $mediaUrl = $msg->link_url;
        }

        $documentUrl = !empty($msg->document_url)
            ? asset('storage/' . ltrim($msg->document_url, '/'))
            : null;
     
        $senderGroupMember = $this->groupRepo->groupMemberModel
        ->where('group_id', $msg->group_id)
        ->where('user_id', $msg->sender_id)
        ->first();

        return [
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
            'sender' => [
                'id' => $sender->id ?? null,
                'name' => $sender->name ?? null,
                'image' => $sender ? $this->getFirstImage($sender->images) : null,
                'status' => $senderGroupMember->status ?? 0,
                'is_member_permission' => (int) ($senderGroupMember->is_member_permission ?? 1) === 1 ? true : false,
                'is_delete' => $sender->is_delete ?? 0,
            ],
        ];
    }

    /**
     * Get first image from image array/json.
     *
     * @param mixed $images
     * @return string|null
     */
    private function getFirstImage($images): ?string
    {
        $imagesArray = getImagesArray($images);
        return !empty($imagesArray) ? $imagesArray[0] : null;
    }
}