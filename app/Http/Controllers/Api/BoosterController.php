<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\BoosterService;

class BoosterController extends BaseController
{
    protected BoosterService $boosterService;
    public function __construct(BoosterService $boosterService)
    {
        $this->boosterService = $boosterService;
    }
    public function activeBooster(Request $request): JsonResponse
    {
        try {
            $getUserId=getUserId();
            $getUserDetails = $this->boosterService->toggleBoosterStatus($getUserId);
            return $this->sendResponse($getUserDetails, __('message.booster_activated_successfully'));
        } catch (Exception $e) {
            Log::error('Error in activeBooster: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_activate_booster'));
        }
    }

    public function runningBooster(Request $request): JsonResponse
    {
        try {
            $getUserId=getUserId();
            $boosterDetail = $this->boosterService->checkActiveBoosterSwissTime($getUserId);
            return $this->sendResponse($boosterDetail, $boosterDetail['is_active']
                ? 'Booster is currently active.'
                : 'No active booster found.');
        } catch (Exception $e) {
            Log::error('Error in activeBooster: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_activate_booster'));
        }
    }

    public function inactiveBooster(Request $request): JsonResponse
    {
        try {
            $getUserId=getUserId();
            $getUserDetails = $this->boosterService->inactivateBoosterPlan($getUserId);
            return $this->sendResponse($getUserDetails, __('message.booster_deactivated_successfully'));
        } catch (Exception $e) {
            Log::error('Error in inactiveBooster: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_deactivate_booster'));
        }
    }
}
