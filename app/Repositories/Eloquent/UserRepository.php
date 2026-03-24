<?php



namespace App\Repositories\Eloquent;



use App\Models\User;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\MobileNotificationModel;
use Illuminate\Support\Facades\Log;

class UserRepository extends BaseRepository implements UserRepositoryInterface

{

    protected $model, $MobileNotificationModel;



    public function __construct(User $model, MobileNotificationModel $MobileNotificationModel)

    {

        $this->model = $model;
        $this->MobileNotificationModel = $MobileNotificationModel;

        parent::__construct($model);
    }


    public function createMobileNotification(
        $sender_user_id,
        $receiver_user_id,
        $title,
        $body,
        $other = [],
        $title_translation = [],
        $body_translation = []
    ) {
        try {
            
            $notificationData['sender_user_id'] = $sender_user_id;
            $notificationData['receiver_user_id'] = $receiver_user_id;
            $notificationData['title'] = $title;
            $notificationData['title_translation'] = $title_translation;
            $notificationData['body'] = $body;
            $notificationData['body_translation'] = $body_translation;
            $notificationData['other'] = json_encode($other, JSON_UNESCAPED_UNICODE);
            $notificationData['read'] = 0;

            return $this->MobileNotificationModel->create($notificationData);
        } catch (\Exception $e) {
            Log::error("Error in UserRepository.createMobileNotification(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
    public function getMobileNotification($byWhere)
    {
        try {
            return $this->MobileNotificationModel->where($byWhere)->orderBy('id', 'desc')->get();
        } catch (\Exception $e) {
            Log::error("Error in userRepository.getMobileNotification(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getUnreadNotificationCount($user_id)
    {
        try {
            return $this->MobileNotificationModel->where(['receiver_user_id' => $user_id, 'read' => 0])->count();
        } catch (\Exception $e) {
            Log::error("Error in userRepository.getUnreadNotificationCount(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function markNotificationsAsRead($user_id)
    {
        try {
            return $this->MobileNotificationModel->where(['receiver_user_id' => $user_id, 'read' => 0])->update(['read' => 1]);
        } catch (\Exception $e) {
            Log::error("Error in userRepository.markNotificationsAsRead(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function readNotifications($byWhere, $updateData)
    {
        try {
            return $this->MobileNotificationModel->whereIn('id', $byWhere)->update($updateData);
        } catch (\Exception $e) {
            Log::error("Error in userRepository.readNotifications(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }

    public function getNotificationsWithPagination($byWhere, $perPage, $page)
    {
        try {
            return $this->MobileNotificationModel
                ->where($byWhere)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
        } catch (\Exception $e) {
            Log::error("Error in userRepository.getNotificationsWithPagination(): " . $e->getMessage());
            return response()->json(['status' => '0', 'message' => __('message.statusZero')]);
        }
    }
}
