<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\PinMarkLikeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PinMarkLikeController extends BaseController
{
    protected PinMarkLikeService $pinMarkLikeService;

    public function __construct(PinMarkLikeService $pinMarkLikeService)
    {
        $this->pinMarkLikeService = $pinMarkLikeService;
    }

    public function toggleLike(Request $request)
    {
        try {
            $result = $this->pinMarkLikeService
                ->toggleLike($request->all());

            return $this->sendResponse($result, __('message.pin_like_updated_successfully'));

        } catch (Exception $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                ['error' => $e->getMessage()]
            );

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
     public function likedUsers(Request $request)
    {
        try {
            $users = $this->pinMarkLikeService
                ->fetchLikedUsers($request->all());
    
            return $this->sendResponse($users, __('message.liked_users_fetched_successfully'));
    
        } catch (\Exception $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                ['error' => $e->getMessage()]
            );
    
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
