<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Repositories\Eloquent\GroupRepository;
use Pusher\Pusher;

class ChatSocketService
{
    protected ?Pusher $pusher = null;
    protected GroupRepository $groupRepo;

    public function __construct(GroupRepository $groupRepo)
    {
        $this->groupRepo = $groupRepo;
        try {
            $this->pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Pusher initialization failed', [
                'error' => $e->getMessage(),
            ]);

            $this->pusher = null;
        }
    }

    public function trigger(string $channel, string $event, array $payload = []): bool
    {
        try {
            if (!$this->pusher) {
                return false;
            }

            $this->pusher->trigger($channel, $event, $payload);

            return true;
        } catch (\Throwable $e) {
            Log::error('Pusher trigger failed', [
                'channel' => $channel,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function groupUpdatesocket(int $groupId): bool
    {
        try {
            if (!$this->pusher) {
                return false;
            }
            $group = $this->groupRepo->getOneData(
                ['id' => $groupId],
                ['creator', 'members.user']
            );
            $groupMemberModel = $this->groupRepo->groupMemberModel;

            $counts = $groupMemberModel
                ->where('group_id', $groupId)
                ->selectRaw("
                    COUNT(CASE WHEN group_status = 'accept' AND is_delete = 0 THEN 1 END) as subscriber_count,
                    COUNT(CASE WHEN group_status = 'pending' AND is_delete = 0 THEN 1 END) as request_count
                ")
                ->first();

            $subscriberCount = (int) $counts->subscriber_count;
            $requestCount    = (int) $counts->request_count;

            $userMembers = $groupMemberModel
                ->with('user')
                ->where('group_id', $groupId)
                ->get();
            foreach($userMembers as $userMember){
                $payload = [
                    'group' => [
                        'group_id' => $group->id,
                        'name' => $group->name,
                        'description' => $group->description,
                        'image' => getImageUrl($group->image),
                        'group_type' => (int) $group->group_type,
                        'is_member_permission' => (int) $group->is_member_permission == 1,
                        'created_by' => $group->created_by,
                        'subscriber_user_count' => $subscriberCount,
                        'user_request_count' => $requestCount,
                        'notification_status' => (int) ($group->notification_status ?? 0),
                        'unread_count' => $userMember ? (int) $userMember->unread_count : 0,
                        'user_detail' => ($userMember && $userMember->user)
                            ? $this->formatGroupMember($userMember)
                            : null,
                    ],
                ];
                $channel = 'groupdetail-' . $groupId . '-' . $userMember->user->id;
                $event = 'group.detail';
                $this->pusher->trigger($channel, $event, $payload);
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('Pusher trigger failed', [
                'channel' => [],
                'event' => [],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

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
}