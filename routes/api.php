<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserRegisterController;

use App\Http\Controllers\Api\SocialLoginController;

use App\Http\Controllers\Api\ContentController;

use App\Http\Controllers\Api\FaqController;

use App\Http\Controllers\Api\ContactUsController;

use App\Http\Controllers\Api\HomeController;

use App\Http\Controllers\Api\TransactionController;

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\BoosterController;

use App\Http\Controllers\Api\{FriendshipController,GroupController, MessageController, PinMarkController, PinMarkCommentController, PinMarkLikeController};


Route::middleware(['setlang'])->group(function () {

Route::get('get', function () {

    return response()->json(['message' => 'API is working']);
});

Route::post('register', [UserRegisterController::class, 'register']);

Route::post('verify-otp', [UserRegisterController::class, 'verifyOtp'])->name('verify-otp');
Route::post('upload-images', [UserRegisterController::class, 'uploadImages']);
Route::post('upload-image', [UserRegisterController::class, 'uploadImage']);
Route::post('upload-document', [MessageController::class, 'uploadDocument']);

Route::middleware('auth:api')->group(function () {


    Route::post('complete-profile', [UserRegisterController::class, 'completeProfile']);

    // Add get-user-detail route (returns user_info inside data object)
    Route::get('get-user-detail', [UserRegisterController::class, 'getUserDetail']);
    Route::post('delete-user-detail', [UserRegisterController::class, 'deleteUserDetail']);
    Route::get('user-detail', [UserRegisterController::class, 'userDetail']);


    // Edit profile (all fields optional)
    Route::post('edit-profile', [UserRegisterController::class, 'editProfile']);
    Route::get('user-notification-setting', [UserRegisterController::class, 'getNotificationSetting']);
    Route::post('user-notification-setting', [UserRegisterController::class, 'postNotificationSettings']);

    Route::post('update-location-consent', [UserRegisterController::class, 'updateLocationConsent']); // Add this line

    Route::post('add-location', [UserRegisterController::class, 'addLocation']); // Add this line
    Route::post('device-token', [UserRegisterController::class, 'deviceToken']);
    Route::get('get-interval', [UserRegisterController::class, 'getInterval']);
    Route::post('updateLatLng', [UserRegisterController::class, 'updateLatLng']);

    // Friendship routes
    Route::post('friend-request', [FriendshipController::class, 'sendRequest']);
    Route::post('friend-request/accept', [FriendshipController::class, 'acceptRequest']);
    Route::post('friend-request/delete', [FriendshipController::class, 'deleteRequest']);
    Route::get('friend-requests/pending', [FriendshipController::class, 'pendingRequests']);
    Route::get('friend-requests/sent', [FriendshipController::class, 'sentRequests']);
    Route::get('friends', [FriendshipController::class, 'friendsList']);

    // Block routes
    Route::post('block-user', [FriendshipController::class, 'blockUser']);
    Route::post('unblock-user', [FriendshipController::class, 'unblockUser']);
    Route::get('blocked-users', [FriendshipController::class, 'blockedUsersList']);
    Route::get('block-status/{id}', [FriendshipController::class, 'checkUserBlocked']);

    Route::get('home', [HomeController::class, 'getHomeUsers']);
    // Map API with distance filtering
    Route::get('map', [HomeController::class, 'getMapUsers']);
    Route::get('notification', [FriendshipController::class, 'notification']);

    Route::post('send-message', [MessageController::class, 'sendMessage']);
    Route::get('group/{group_id}/messages', [MessageController::class, 'groupChatHistory']);
    Route::get('conversation/{user_id}', [MessageController::class, 'getMessages']); // 1-to-1 chat history
    Route::delete('delete-message/{message_id}', [MessageController::class, 'deleteMessage']); // delete message
    Route::get('sent-message-users', [MessageController::class, 'sentMessageUsers']);
    Route::post('group/create', [MessageController::class, 'createGroup']); // Add this line

    // Add this line for checking group name
    Route::post('group/check-name', [MessageController::class, 'checkGroupName']);

    // Add this line for group listing with pagination
    Route::get('groups', [MessageController::class, 'listGroups']);

    Route::post('group/join', [MessageController::class, 'joinGroup']); // Join group (public/private)
    Route::post('group/handle-join-request', [MessageController::class, 'handleJoinRequest']); // Accept/reject join request
    Route::delete('conversation/{user_id}/delete-all', [MessageController::class, 'deleteAllConversation']);
    // Add this line for deleting all messages in a group chat
    Route::delete('group/{group_id}/delete-all', [MessageController::class, 'deleteAllGroupMessages']);
    Route::delete('group-admin/{group_id}/delete-all', [MessageController::class, 'deleteAllAdminGroupMessages']);


    // Add this line for all group listing and search
    Route::get('all-groups', [MessageController::class, 'searchGroups']);

    Route::post('group/add-member', [MessageController::class, 'addMemberToGroup']); // Add member to group
    Route::post('group/remove-member', [MessageController::class, 'removeMemberFromGroup']); // Remove member from group
    Route::post('group/block-member', [MessageController::class, 'blockGroupMember']); // Block group member
    Route::post('group/unblock-member', [MessageController::class, 'unblockGroupMember']); // Unblock group member

    // Group member permission APIs
    Route::post('group/update-permission-all', [MessageController::class, 'updateGroupPermissionForAll']);
    Route::post('group/update-permission-member', [MessageController::class, 'updateGroupPermissionForMember']);
    Route::get('group/member-permission', [MessageController::class, 'getGroupMemberPermission']);

    // Group conversations (group chat list)
    Route::get('group/conversations', [MessageController::class, 'groupConversations']);

    // Delete group (admin only)
    Route::delete('group/delete', [MessageController::class, 'deleteGroup']);

    Route::get('group/blocked-members', [MessageController::class, 'blockedGroupMembers']);
    Route::get('group/details', [MessageController::class, 'groupDetails']);
    Route::post('group/edit', [MessageController::class, 'editGroup']);

    Route::post('group/leave', [MessageController::class, 'leaveGroup']); // Leave group

    // Add this line for reporting a group
    Route::post('group/report', [MessageController::class, 'reportGroup']);
    Route::post('user/report', [MessageController::class, 'userGroup']);
    Route::post('pin/report', [MessageController::class, 'pinGroup']);

    Route::post('group/media-list', [MessageController::class, 'groupMediaList']);

    Route::get('latest-message', [MessageController::class, 'latestMessage']);
    // Optionally remove these if you want only one endpoint:
    // Route::get('group/{group_id}/latest-message', [MessageController::class, 'latestGroupMessage']);
    // Route::get('conversation/{user_id}/latest-message', [MessageController::class, 'latestIndividualMessage']);
    
    Route::post('individual-media-list', [MessageController::class, 'individualMediaList']);

    Route::get('individual-notification-status', [MessageController::class, 'getIndividualNotificationStatus']);
    Route::post('individual-notification-status', [MessageController::class, 'setIndividualNotificationStatus']);

    Route::post('transaction/success', [TransactionController::class, 'success']);
    
    Route::post('pin-mark', [PinMarkController::class, 'pinMark']);
    Route::get('pin-mark/fetch', [PinMarkController::class, 'pinMarkFetch']);
    Route::delete('pin-mark/{id}', [PinMarkController::class, 'deletePinMark']);
    
    Route::post('pin-mark-comment', [PinMarkCommentController::class, 'pinMarkComment']);
    Route::get('pin-mark-comment/fetch', [PinMarkCommentController::class, 'pinMarkCommentFetch']);
    Route::delete('pin-mark-comment/{id}', [PinMarkCommentController::class, 'deletePinMarkComment']);
    Route::post('pin-mark/like', [PinMarkLikeController::class, 'toggleLike']);
    Route::get('pin-mark/liked-users', [PinMarkLikeController::class, 'likedUsers']);


    // notification controller
    Route::get('delete/notification', [NotificationController::class, 'deleteNotification']);
    Route::post('active-booster', [BoosterController::class, 'activeBooster']);
    Route::get('running-booster', [BoosterController::class, 'runningBooster']);
    Route::get('inactive-booster', [BoosterController::class, 'inactiveBooster']);
        
    Route::post('update-user-plan', [HomeController::class, 'updateUserPlan']);

    // New Group Message APIs
    Route::prefix('get/group/')->group(function () {
        Route::get('details', [GroupController::class, 'groupDetails']);
        Route::get('members', [GroupController::class, 'groupMembers']);
        Route::get('requests', [GroupController::class, 'groupRequests']);
        Route::get('messages', [GroupController::class, 'groupMessages']);
        Route::get('block-user', [GroupController::class, 'blockUser']);
    });
});

Route::post('social-login', [SocialLoginController::class, 'socialLogin']);

Route::get('content', [ContentController::class, 'show']);

Route::get('faq', [ContentController::class, 'faq']);

Route::post('contact-us', [ContactUsController::class, 'store']);

Route::get('interests', [ContentController::class, 'getInterests']);

Route::get('get-sub-interests/{interestId}', [ContentController::class, 'getSubInterests']);
Route::get('get-ghost-plans', [HomeController::class, 'getGhostPlans']);

Route::get('get-boost-plans', [HomeController::class, 'getBoostPlans']);
Route::get('get-pin-plans', [HomeController::class, 'getPinPlans']);

});

