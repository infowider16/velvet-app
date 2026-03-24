<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use Exception;
use Illuminate\Support\Facades\Log;


class NotificationController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function deleteNotification(Request $request)
    {
        try {

            $userId = $request->input('user_id');
            if (empty($userId)) {
                return $this->sendError(__('message.the_userid_field_is_required'), [], 422);
            }

            $data = $this->notificationService->deleteNotification($userId);
            return $this->sendResponse($data['data'], $data['message']);
        } catch (Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage(),
                ['request' => $request->all()]
            );

            return $this->sendError(__('message.internal_server_error'), [], 500);
        }
    }
}
