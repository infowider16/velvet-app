<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Services\PinMarkCommentService;
use Exception;
use Illuminate\Support\Facades\Log;

class PinMarkCommentController extends BaseController
{
    protected $pinMarkCommentService;

    public function __construct(PinMarkCommentService $pinMarkCommentService)
        {
        $this->pinMarkCommentService = $pinMarkCommentService;
    }

    public function pinMarkComment(Request $request)
        {
        try {
            $allData=$request->all();
            $pinMarkComment=$this->pinMarkCommentService->storePinMarkComment($allData);
            return $this->sendResponse($pinMarkComment, __('message.pin_mark_comment_fetched_successfully'));
        } catch (Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage()
            );

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function pinMarkCommentFetch(Request $request)
        {
            try {
                $filters = $request->all();
        
                $markComments = $this->pinMarkCommentService->fetchPinMarkComments($filters);
        
                return $this->sendResponse($markComments, __('message.pin_mark_comments_fetched_successfully'));
            } catch (Exception $e) {
                Log::error(
                    "Error in " . __CLASS__ . "::" . __FUNCTION__,
                    ['error' => $e->getMessage()]
                );
        
                return $this->sendError($e->getMessage(), [], 500);
            }
        }
    
    public function deletePinMarkComment(int $id)
        {
    try {
        $result = $this->pinMarkCommentService->deletePinMarkComment($id);

        if (!$result) {
            return $this->sendError(__('message.pin_mark_comment_not_found'), [], 404);
        }

        return $this->sendResponse([], __('message.pin_mark_comment_deleted_successfully'));
    } catch (\Exception $e) {
        Log::error(
            "Error in " . __CLASS__ . "::" . __FUNCTION__,
            ['error' => $e->getMessage(), 'id' => $id]
        );

        return $this->sendError($e->getMessage(), [], 500);
    }
}


}
