<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\MessageService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\{CreateGroupRequest, AddMemberRequest};
use App\Repositories\Eloquent\GroupRepository;
use Illuminate\Validation\ValidationException;
use App\Traits\UploadImageTrait;

class MessageController extends BaseController
{
    use UploadImageTrait;

    protected $messageService;
    protected $groupRepository;

    public function __construct(MessageService $messageService, GroupRepository $groupRepository)
    {
        $this->messageService = $messageService;
        $this->groupRepository = $groupRepository;
    }

    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->sendMessage($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in sendMessage: ' . $e->getMessage());
            return $this->sendError(__('message.send_message_failed'));
        }
    }

    public function groupChatHistory(Request $request, $group_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->getGroupChatHistory($user->id, $group_id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in groupChatHistory: ' . $e->getMessage());
            return $this->sendError(__('message.group_chat_history_failed'));
        }
    }

    public function getMessages(Request $request, $user_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->getMessages($user->id, $user_id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in getMessages: ' . $e->getMessage());
            return $this->sendError(__('message.chat_history_failed'));
        }
    }

    public function deleteMessage(Request $request, $message_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->deleteMessage($user->id, $message_id);

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in deleteMessage: ' . $e->getMessage());
            return $this->sendError(__('message.delete_message_failed'));
        }
    }

    public function sentMessageUsers(Request $request): JsonResponse
    {
        try {
         
            $user = $this->getAuthenticatedUserOrError($request);
 
            if ($user instanceof JsonResponse) {
                return $user;
            }
          
            $result = $this->messageService->getSentMessageUsers($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in sentMessageUsers: ' . $e->getMessage());
            return $this->sendError(__('message.sent_message_users_failed'));
        }
    }

    public function createGroup(CreateGroupRequest $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->createGroup($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in createGroup: ' . $e->getMessage());
            return $this->sendError(__('message.create_group_failed'));
        }
    }

    public function joinGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->joinGroup($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in joinGroup: ' . $e->getMessage());
            return $this->sendError(__('message.join_group_failed'));
        }
    }

    public function handleJoinRequest(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->handleJoinRequest($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in handleJoinRequest: ' . $e->getMessage());
            return $this->sendError(__('message.handle_join_request_failed'));
        }
    }

    public function deleteAllConversation(Request $request, $user_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->deleteAllConversation($user->id, $user_id);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in deleteAllConversation: ' . $e->getMessage());
            return $this->sendError(__('message.delete_all_conversation_failed'));
        }
    }

    public function checkGroupName(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $name = $request->input('name');
            $exists = $this->groupRepository->isGroupNameExists($name);

            if ($exists) {
                $data['exists'] = true;
                return $this->sendResponse($data, __('message.group_name_taken'));
            }

            return $this->sendResponse(['exists' => false], __('message.group_name_available'));
        } catch (Exception $e) {
            \Log::error('Error in checkGroupName: ' . $e->getMessage());
            return $this->sendError(__('message.search_groups_failed'));
        }
    }

    public function listGroups(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->getGroups($user->id, $request->all());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in getMessages: ' . $e->getMessage());
            return $this->sendError(__('message.chat_history_failed'));
        }
    }

    public function searchGroups(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $page = (int) $request->input('page', 1);
            $keyword = trim($request->input('search', ''));

            $result = $this->messageService->searchGroups($keyword, $perPage, $page);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in searchGroups: ' . $e->getMessage());
            return $this->sendError(__('message.search_groups_failed'));
        }
    }

    public function addMemberToGroup(AddMemberRequest $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $userIds = $request->input('user_id');

            if (
                is_array($userIds) &&
                count($userIds) === 1 &&
                is_string($userIds[0]) &&
                str_starts_with(trim($userIds[0]), '[')
            ) {
                $decoded = json_decode($userIds[0], true);
                if (is_array($decoded)) {
                    $userIds = $decoded;
                }
            } elseif (!is_array($userIds)) {
                $userIds = [$userIds];
            }

            $data = $request->all();
            $data['user_ids'] = $userIds;
            unset($data['user_id']);

            $result = $this->messageService->addMemberToGroup($user->id, $data);

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error in addMemberToGroup: ' . $e->getMessage());
            return $this->sendError(__('message.add_member_failed'));
        }
    }

    public function removeMemberFromGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $result = $this->messageService->removeMemberFromGroup($user->id, $request->all());

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            \Log::error('Error in removeMemberFromGroup: ' . $e->getMessage());
            return $this->sendError(__('message.remove_member_failed'));
        }
    }

    public function blockGroupMember(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $result = $this->messageService->blockOrUnblockGroupMember($user->id, $request->all(), 1);

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('message.validation_failed'),
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in blockGroupMember: ' . $e->getMessage());
            return $this->sendError(__('message.block_member_failed'));
        }
    }

    public function unblockGroupMember(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $result = $this->messageService->blockOrUnblockGroupMember($user->id, $request->all(), 0);

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => __('message.validation_failed'),
                'errors' => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error in unblockGroupMember: ' . $e->getMessage());
            return $this->sendError(__('message.unblock_member_failed'));
        }
    }

    public function updateGroupPermissionForAll(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'is_member_permission' => 'required|boolean',
            ]);

            $result = $this->messageService->updateGroupPermissionForAll($user->id, $request->all());

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            \Log::error('Error in updateGroupPermissionForAll: ' . $e->getMessage());
            return $this->sendError(__('message.update_permission_all_failed'));
        }
    }

    public function updateGroupPermissionForMember(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'user_id' => 'required|integer|exists:users,id',
                'is_member_permission' => 'required|boolean',
            ]);

            $result = $this->messageService->updateGroupPermissionForMember($user->id, $request->all());

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            \Log::error('Error in updateGroupPermissionForMember: ' . $e->getMessage());
            return $this->sendError(__('message.update_permission_member_failed'));
        }
    }

    public function getGroupMemberPermission(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'group_id' => 'required|integer|exists:groups,id',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $result = $this->messageService->getGroupMemberPermission($user->id, $request->all());

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'] ?? [], $result['message']);
        } catch (ValidationException $e) {
            return $this->sendError($e->validator->errors()->first(), 422);
        } catch (\Exception $e) {
            \Log::error('Error in getGroupMemberPermission: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_group_member_permission_failed'));
        }
    }

    public function groupConversations(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = $request->input('group_id');

            if ($groupId) {
                $result = $this->messageService->getGroupConversationDetail($user->id, $groupId, $request->all());

                if (isset($result['error']) && $result['error']) {
                    return $this->sendError($result['message'], $result['code'] ?? 400);
                }

                return $this->sendResponse($result['data'], $result['message']);
            }

            return $this->sendError(__('message.group_id_required'), 400);
        } catch (\Exception $e) {
            \Log::error('Error in groupConversations: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_group_conversations_failed'));
        }
    }

    public function deleteGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $result = $this->messageService->deleteGroup($user->id, $request->input('group_id'));

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in deleteGroup: ' . $e->getMessage());
            return $this->sendError(__('message.delete_group_failed'));
        }
    }

    public function blockedGroupMembers(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $groupId = $request->input('group_id');
            $blockedMembers = $this->messageService->getBlockedGroupMembers($user->id, $groupId);

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => __('message.blocked_group_members_fetched'),
                'error_code' => 0,
                'data' => $blockedMembers,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in blockedGroupMembers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.blocked_group_members_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function groupDetails(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $groupId = $request->input('group_id');
            $result = $this->messageService->getGroupDetails($user->id, $groupId, $request->all());

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in groupDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.fetch_group_details_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function editGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $allowedFields = [
                'group_id',
                'name',
                'description',
                'image',
                'group_type',
                'is_member_permission',
                'notification_status',
            ];

            $input = $request->only($allowedFields);

            if (empty($input['group_id'])) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => __('message.group_id_required'),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            foreach ($request->all() as $key => $value) {
                if (!in_array($key, $allowedFields)) {
                    return response()->json([
                        'success' => false,
                        'status' => 0,
                        'message' => __('message.invalid_field', ['field' => $key]),
                        'error_code' => 400,
                        'data' => null,
                    ], 200);
                }
            }

            $result = $this->messageService->editGroup($user->id, $input);

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in editGroup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.update_group_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function leaveGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $result = $this->messageService->leaveGroup($user->id, $request->input('group_id'));

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in leaveGroup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.leave_group_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function uploadDocument(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png|max:10240',
            ], [
                'document.required' => __('message.document_required'),
                'document.file' => __('message.invalid_file_upload'),
                'document.mimes' => __('message.document_mimes'),
                'document.max' => __('message.document_max'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 422,
                    'data' => null,
                ], 200);
            }

            $file = $request->file('document');

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => __('message.document_not_uploaded'),
                    'error_code' => 422,
                    'data' => null,
                ], 200);
            }

            $path = $this->uploadImage($file, 'documents');

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => __('message.document_uploaded'),
                'error_code' => 0,
                'data' => [
                    'path' => $path,
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in uploadDocument: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.document_upload_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function reportGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
                'reason' => 'required|string|max:1000',
                'report_type' => 'required|string|max:255',
                'email' => 'sometimes|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 422,
                    'data' => null,
                ], 200);
            }

            $result = $this->messageService->reportGroup(
                $user->id,
                $request->only(['group_id', 'report_type', 'reason', 'email', 'image'])
            );

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'] ?? null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.report_group_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function userGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'reason' => 'required|string|max:1000',
                'report_type' => 'required|string|max:255',
                'email' => 'sometimes|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 422,
                    'data' => null,
                ], 200);
            }

            $result = $this->messageService->userGroup(
                $user->id,
                $request->only(['user_id', 'report_type', 'reason', 'email', 'image'])
            );

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'] ?? null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.report_group_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function pinGroup(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'pin_id' => 'required|integer|exists:users,id',
                'reason' => 'required|string|max:1000',
                'report_type' => 'required|string|max:255',
                'email' => 'sometimes|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 422,
                    'data' => null,
                ], 200);
            }

            $result = $this->messageService->pinGroup($user->id, $request->all());

            if (isset($result['error']) && $result['error']) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $result['message'],
                    'error_code' => $result['code'] ?? 400,
                    'data' => null,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'] ?? null,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in reportGroup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.report_group_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function groupMediaList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'group_id' => 'required|integer|exists:groups,id',
                'type' => 'required|integer|in:1,2,3',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $groupId = $request->input('group_id');
            $type = (int) $request->input('type');
            $perPage = (int) $request->input('per_page', 20);
            $page = (int) $request->input('page', 1);

            $result = $this->messageService->getGroupMediaList($groupId, $type, $perPage, $page);

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in groupMediaList: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.group_media_list_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function individualMediaList(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $validator = \Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'type' => 'required|integer|in:1,2,3',
                'per_page' => 'sometimes|integer|min:1|max:100',
                'page' => 'sometimes|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    'error_code' => 400,
                    'data' => null,
                ], 200);
            }

            $otherUserId = $request->input('user_id');
            $type = (int) $request->input('type');
            $perPage = (int) $request->input('per_page', 20);
            $page = (int) $request->input('page', 1);

            $result = $this->messageService->getIndividualMediaList($user->id, $otherUserId, $type, $perPage, $page);

            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => $result['message'],
                'error_code' => 0,
                'data' => $result['data'],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in individualMediaList: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 0,
                'message' => __('message.individual_media_list_failed'),
                'error_code' => 500,
                'data' => null,
            ], 200);
        }
    }

    public function latestMessage(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $groupId = $request->input('group_id');
            $userId = $request->input('user_id');
            $limit = (int) $request->input('limit', 20);
            $createdAt = $request->input('created_at');

            if ($groupId) {
                $result = $this->messageService->getLatestGroupMessage($user->id, $groupId, $limit, $createdAt);
            } elseif ($userId) {
                $result = $this->messageService->getLatestIndividualMessage($user->id, $userId, $limit, $createdAt);
            } else {
                return $this->sendError(__('message.either_group_or_user_required'), 422);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error in latestMessage: ' . $e->getMessage());
            return $this->sendError(__('message.latest_message_failed'));
        }
    }

    public function deleteAllGroupMessages(Request $request, $group_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->deleteAllGroupMessages($user->id, $group_id);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            \Log::error('Error in deleteAllGroupMessages: ' . $e->getMessage());
            return $this->sendError(__('message.delete_all_group_messages_failed'));
        }
    }

    public function deleteAllAdminGroupMessages(Request $request, $group_id): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $result = $this->messageService->deleteAllAdminGroupMessages($user->id, $group_id);

            if (isset($result['error']) && $result['error']) {
                return $this->sendError($result['message'], $result['code'] ?? 400);
            }

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error in deleteAllAdminGroupMessages: ' . $e->getMessage());
            return $this->sendError(__('message.delete_all_admin_group_messages_failed'));
        }
    }

    public function getIndividualNotificationStatus(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $otherUserId = $request->input('user_id');
            $result = $this->messageService->getIndividualNotificationStatus($user->id, $otherUserId);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            \Log::error('Error in getIndividualNotificationStatus: ' . $e->getMessage());
            return $this->sendError(__('message.fetch_notification_status_failed'));
        }
    }

    public function setIndividualNotificationStatus(Request $request): JsonResponse
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {
                return $user;
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'notification_status' => 'required|boolean',
            ]);

            $otherUserId = $request->input('user_id');
            $status = (bool) $request->input('notification_status');
            $result = $this->messageService->setIndividualNotificationStatus($user->id, $otherUserId, $status);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (\Exception $e) {
            Log::error('Error in setIndividualNotificationStatus: ' . $e->getMessage());
            return $this->sendError(__('message.update_notification_status_failed'));
        }
    }
}