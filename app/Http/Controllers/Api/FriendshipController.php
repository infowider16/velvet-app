<?php

namespace App\Http\Controllers\Api;



use App\Http\Controllers\BaseController;

use App\Services\FriendshipService;

use Exception;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class FriendshipController extends BaseController
{
    protected $friendshipService;

    public function __construct(FriendshipService $friendshipService)
    {
        $this->friendshipService = $friendshipService;
    }

    public function sendRequest(Request $request): JsonResponse
    {
        try {
             $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }
           
            $result = $this->friendshipService->sendFriendRequest($user->id, $request->friend_id);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function acceptRequest(Request $request): JsonResponse
    {
        try {
            
             $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }
            $result = $this->friendshipService->acceptFriendRequest($user->id, $request->friend_id);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function deleteRequest(Request $request): JsonResponse
    {
        try {
             $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }
            $result = $this->friendshipService->deleteFriendRequest($user->id, $request->friend_id);
            return $this->sendResponse([], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function pendingRequests(Request $request): JsonResponse
    {
        try {
               $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }
            $result = $this->friendshipService->getPendingRequests($user->id, $request);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function sentRequests(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }
            $result = $this->friendshipService->getSentRequests($user->id, $request);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function friendsList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }
            $result = $this->friendshipService->getFriendsList($user->id, $request);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

     public function blockUser(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'blocked_user_id' => 'required|integer|exists:users,id'
            ]);

            $result = $this->friendshipService->blockUser($user->id, $request->blocked_user_id);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function unblockUser(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'blocked_user_id' => 'required|integer|exists:users,id'
            ]);

            $result = $this->friendshipService->unblockUser($user->id, $request->blocked_user_id);
            return $this->sendResponse([], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function blockedUsersList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->friendshipService->getBlockedUsersList($user->id, $request);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function notification(Request $request): JsonResponse
    {
        try {
            
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->friendshipService->getNotifications($user->id, $request);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }
}