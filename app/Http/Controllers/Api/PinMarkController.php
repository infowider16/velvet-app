<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Services\PinMarkService;
use Exception;
use Illuminate\Support\Facades\Log;

class PinMarkController extends BaseController
{
    protected $pinMarkService;

    public function __construct(PinMarkService $pinMarkService)
        {
        $this->pinMarkService = $pinMarkService;
    }

    public function pinMark(Request $request)
        {
        try {
            $allData=$request->all();
            $pinMark=$this->pinMarkService->storePinMark($allData);
            return $this->sendResponse($pinMark, __('message.pin_mark_fetched_successfully'));
        } catch (Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage()
            );

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function pinMarkFetch(Request $request)
    {
        try {
            $filters = $request->all();
    
            $marks = $this->pinMarkService->fetchPinMarks($filters);
    
            return $this->sendResponse($marks, __('message.pin_marks_fetched_successfully'));
        } catch (Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__,
                ['error' => $e->getMessage()]
            );
    
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function deletePinMark(int $id)
        {
    try {
        $result = $this->pinMarkService->deletePinMark($id);

        if (!$result) {
            return $this->sendError(__('message.pin_mark_not_found'), [], 404);
        }

        return $this->sendResponse([], __('message.pin_mark_deleted_successfully'));
    } catch (\Exception $e) {
        Log::error(
            "Error in " . __CLASS__ . "::" . __FUNCTION__,
            ['error' => $e->getMessage(), 'id' => $id]
        );

        return $this->sendError($e->getMessage(), [], 500);
    }
}


}
