<?php

namespace App\Services;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\MobileNotificationModel;

class NotificationService
{
    

    public function deleteNotification($userId)
    {
        try {

            $deleted = MobileNotificationModel::where('receiver_user_id', $userId)->delete();
            
            return [
                "data" => ['user_id' => $userId, 'deleted' => $deleted],
                "message" => $deleted > 0 ? __('message.notifications_deleted_successfully') : __('message.no_notifications_found_to_delete')
            ];

        } catch (\Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__,
                ['error' => $e->getMessage(), 'data' => $requestDatas]
            );

            return null;
        }
    }


   
}
