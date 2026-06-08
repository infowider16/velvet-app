<?php

namespace App\Services;

use App\Repositories\Eloquent\{ PinMarkRepository , PinMarkLikeRepository , FriendshipRepository};
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Friendship;
use App\Models\GhostManagement;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use App\Models\User;

use App\Services\UserRegisterService;

class PinMarkService
{
    protected PinMarkLikeRepository $pinMarkLikeRepo;
    protected PinMarkRepository $pinMarkRepo;
    protected UserRepositoryInterface $userRepo;
    protected FriendshipRepository $friendshipRepo;
    protected UserRegisterService $userRegisterService;

    public function __construct(PinMarkLikeRepository $pinMarkLikeRepo,PinMarkRepository $pinMarkRepo, UserRepositoryInterface $userRepo,UserRegisterService $userRegisterService,FriendshipRepository $friendshipRepo)
    {
        $this->pinMarkLikeRepo = $pinMarkLikeRepo;
        $this->pinMarkRepo = $pinMarkRepo;
        $this->userRepo = $userRepo;
        $this->userRegisterService = $userRegisterService;
        $this->friendshipRepo = $friendshipRepo;
    }

    public function storePinMark(array $requestDatas)
        {
        try {

            $this->validateRequiredKeys($requestDatas);

            $userId = (int) $requestDatas['user_id'];

            $swissNowFormatted = convertTimezone(
                Carbon::now(),
                null,
                'Y-m-d H:i:s'
            );

            $requestDatas['created_at']   = $swissNowFormatted;
            $requestDatas['commented_on'] = $swissNowFormatted;
            $requestDatas['total_like'] = 0;

            // Validate pin availability
            $this->validatePinCount($userId);

            // Create Mark
            $pinMark = $this->pinMarkRepo->create($requestDatas);

            // Deduct pin count
            $this->updateDeductPinCount($userId);

            $friendIds  = $this->friendshipRepo->getFriendsIds($userId);
            if (!empty($friendIds)) {

                $friends = User::whereIn('id', $friendIds)
                    ->whereNotNull('device_token')
                    ->get();

                $sender = $this->userRepo->getOneData(['id' => $userId]);
                dd($sender);
                $senderName = $sender->name ?? 'Someone';

                $other = [
                    'pin_id'        => $pinMark->id,
                    'pin_user_id'   => $pinMark->user_id,
                    'screen_name'   => 'friend_pin',
                ];

                foreach ($friends as $friend) {


                    $lang = $friend->lang_key ?? 'en';

                    $title = trans('message.new_pin_title', [], $lang);

                    $body = trans(
                        'message.new_pin_body',
                        ['name' => $senderName],
                        $lang
                    );


                    if ((int) $friend->id === $userId) {
                        continue;
                    }

                    $deviceTokens = is_array($friend->device_token)
                        ? $friend->device_token
                        : json_decode($friend->device_token, true);

                    if (empty($deviceTokens)) {
                        continue;
                    }

                    sendPushNotification(
                        $deviceTokens,
                        $title,
                        $body,
                        $other,
                        [$friend->id],
                        'pin_screen'
                    );
                }
            }

            // Fetch user detail ONCE
            $userResponse = $this->userRegisterService->getUserDetail($userId);

            // Attach only user_info
            $pinMark->user = $userResponse['data']['user_info'] ?? null;

            return $pinMark;

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data'  => $requestDatas
                ]
            );

            return null;
        }
    }

    /**
     * -----------------------
     * Private reusable helpers
     * -----------------------
     */

    private function validateRequiredKeys(array $data): void
        {
        foreach (['pin_message', 'country_code', 'user_id'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                throw ValidationException::withMessages([
                    $field => ucfirst(str_replace('_', ' ', $field)) . ' is required.',
                ]);
            }
        }
    }

    private function validatePinCount(int $userId): void
        {
    $user = $this->userRepo->getOneData(['id' => $userId]);

    if (!$user) {
        throw ValidationException::withMessages([
            'user_id' => 'User not found.',
        ]);
    }

    // ❌ No pins → do not allow
    if ((int) $user->pin_count <= 0) {
        throw ValidationException::withMessages([
            'pin_count' => 'You do not have enough pins to create this Mark.',
        ]);
    }
}

    
    private function updateDeductPinCount(int $userId): int
        {
            $user = $this->userRepo->getOneData(['id' => $userId]);
        
            if (!$user) {
                throw ValidationException::withMessages([
                    'user_id' => 'User not found.',
                ]);
            }
        
            // Deduct 1 pin, minimum 0
            $newPinCount = max(0, ((int) $user->pin_count) - 1);
        
            $this->userRepo->update(
                ['id' => $userId],
                ['pin_count' => $newPinCount]
            );
        
            return $newPinCount;
        }

    private function getUserData( int $userId): array
        {
        $user = $this->userRepo->getOneData(['id' => $userId]);

        $data['user_data'] = $user;

        return $data;
    }
    
    
    public function fetchPinMarks(array $filters = [])
    {
        try {

            $marks = $this->pinMarkRepo->fetch($filters);
       
            $items = method_exists($marks, 'getCollection')
                ? $marks->getCollection()
                : collect($marks);
            $userSecondId = $filters['userSecond_id'] ?? null;

            $items->transform(function ($mark) use ($userSecondId) {

                $user = $this->userRepo->getOneData([
                    'id' => $mark->user_id
                ]);

                $alreadyLiked = false;

                if ($userSecondId) {
                    $alreadyLiked = $this->pinMarkLikeRepo
                        ->exists($mark->id, $userSecondId);
                }

                $mark->pin_liked = $alreadyLiked;

                if ($user) {
                    $mark->user = [
                        'name' => $user->name,
                        'images' => $user->images ?? [],
                        'last_seen_at' => $user->last_seen_at,
                    ];
                } else {
                    $mark->user = null;
                }

                return $mark;
            });

            return $marks;

        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                ['error' => $e->getMessage(), 'filters' => $filters]
            );

            return [];
        }
    }


    public function deletePinMark(int $id): bool
        {
        try {
            return $this->pinMarkRepo->softDeleteById($id);
    
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                ['error' => $e->getMessage(), 'id' => $id]
            );
    
            return false;
        }
    }

  
}
