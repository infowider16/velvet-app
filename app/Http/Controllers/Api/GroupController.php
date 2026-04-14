<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Services\GroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends BaseController
{
    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function groupDetails(Request $request): JsonResponse
    {
        try {

            $user = $this->getAuthenticatedUserOrError($request);
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = (int) $request->input('group_id');
            if (!$groupId) {
                return $this->sendError(__('message.group_id_required'), 400);
            }

            $result = $this->groupService->getGroupDetails($user->id, $groupId);

            if (!empty($result['error'])) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in groupDetails: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_group_details_failed'));
        }
    }

    public function groupMembers(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = (int) $request->input('group_id');
            if (!$groupId) {
                return $this->sendError(__('message.group_id_required'), 400);
            }

            $result = $this->groupService->getGroupMembers($user->id, $groupId, $request->all());

            if (!empty($result['error'])) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in groupMembers: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_group_members_failed'));
        }
    }

    public function groupRequests(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = (int) $request->input('group_id');
            if (!$groupId) {
                return $this->sendError(__('message.group_id_required'), 400);
            }

            $result = $this->groupService->getGroupRequests($user->id, $groupId, $request->all());

            if (!empty($result['error'])) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in groupRequests: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_fetch_group_requests'));
        }
    }

    public function groupMessages(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = (int) $request->input('group_id');
            if (!$groupId) {
                return $this->sendError(__('message.group_id_required'), 400);
            }

            $result = $this->groupService->getGroupMessages($user->id, $groupId, $request->all());

            if (!empty($result['error'])) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in groupMessages: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_group_messages_failed'));
        }
    }

    public function blockUser(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);
            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = (int) $request->input('group_id');
            if (!$groupId) {
                return $this->sendError(__('message.group_id_required'), 400);
            }

            $result = $this->groupService->blockGroupUser($user->id, $groupId, $request->all());

            if (!empty($result['error'])) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in blockUser: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_fetch_blocked_users'));
        }
    }

    
}