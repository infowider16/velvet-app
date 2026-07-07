<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Contracts\Services\AdminUserServiceInterface;

use App\Http\Requests\Admin\UserImageUploadRequest;

use App\Repositories\Eloquent\GroupRepository;

use  App\Models\PinMark;

class UserController extends BaseController

{

    protected AdminUserServiceInterface $userService;
    protected GroupRepository $groupRepository;



    public function __construct(AdminUserServiceInterface $userService, GroupRepository $groupRepository)

    {

        $this->userService = $userService;

        $this->groupRepository = $groupRepository;

    }


    public function index()

    {

        return view('admin.users-list');

    }



    public function userList(Request $request)

    {

        try {

            if ($request->ajax()) {

                return $this->userService->getUserListDataTable();

            }

            return view('admin.users-list');

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function toggleStatus(Request $request)

    {

        try {

            $result = $this->userService->toggleUserStatus($request);

            

            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }

            

            return $this->adminErrorResponse($result['message'], [], [], 0, 404);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function show(Request $request, $id)

    {

        

        try {

            $user = $this->userService->getUserDetail($id);

            

            if ($user) {

                return view('admin.user-detail', compact('user'));

            }

            return back()->with('error', __('message.user_not_found'));

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }

    public function groupMembers(Request $request, $id)
    {
        try {
            return $this->userService->getUserGroupMembersDataTable($request, $id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function groupReports(Request $request, $id)
    {
        try {
            return $this->userService->getUserGroupReportsDataTable($request, $id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function messages(Request $request, $id)
    {
        try {
            return $this->userService->getUserMessagesDataTable($request, $id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function userPins(Request $request, $id)
    {
        try {
            return $this->userService->getUserPinsDataTable($request, $id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function pinComments($id)
    {
        try {
            return $this->userService->getPinComments($id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function pinLikes($id)
    {
        try {
            return $this->userService->getPinLikes($id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function pinReports($id)
    {
        try {
            return $this->userService->getPinReports($id);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return response()->json(['status' => false, 'message' => __('message.some_thing_went_wrong')], 500);
        }
    }

    public function destroy(Request $request, $id)

    {

        try {

            $result = $this->userService->deleteUser($id);



            if (isset($result['status']) && $result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }



            return $this->adminErrorResponse($result['message'] ?? __('message.some_thing_went_wrong'), [], [], 0, 404);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function updatePhone(Request $request, $id)

    {

        try {

            $request->validate([

                // 'phone_code' => 'required|string',

                'phone_number' => 'required|string',

                // 'country_code' => 'required|string'

            ]);



            $result = $this->userService->updateUserPhone(

                $id,

                // $request->phone_code,

                $request->phone_number,

                // $request->country_code

            );



            if (isset($result['status']) && $result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }



            return $this->adminErrorResponse($result['message'] ?? __('message.some_thing_went_wrong'), [], [], 0, 404);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }

    public function deleteImage(Request $request)

    {

        try {
           return $this->userService->deleteUserImage($request);
        }catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }
    }

    public function uploadImage(UserImageUploadRequest $request)
    {
        try {
           
            $result = $this->userService->uploadUserImage($request);

            if (isset($result['status']) && $result['status']) {
                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }

            return $this->adminErrorResponse($result['message'] ?? __('message.some_thing_went_wrong'), [], [], 0, 400);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);
        }
    }

    public function groupDetail($id)
    {
        try {

            $group = $this->groupRepository->getGroupDetail($id);

            if (!$group) {
                return response()->json([
                    'status' => false,
                    'message' => 'Group not found.'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $group
            ]);

        } catch (\Exception $e) {

            Log::error(__METHOD__.' : '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => __('message.some_thing_went_wrong')
            ], 500);
        }
    }
    public function membersDetail($id)
    {
        try {

            $members = $this->groupRepository->getGroupMembersDetail($id);

            return response()->json([
                'status' => true,
                'data' => $members
            ]);

        } catch (\Exception $e) {

            Log::error(__METHOD__.' : '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => __('message.some_thing_went_wrong')
            ], 500);
        }
    }

    public function groupDetailPage($id)
    {
        try {

            $group = $this->groupRepository->getGroupDetail($id);

            if (!$group) {
                return redirect()->back()->with('error', 'Group not found.');
            }

            return view('admin.group.detail', compact('group'));

        } catch (\Exception $e) {

            Log::error(__METHOD__ . ' : ' . $e->getMessage());

            return redirect()->back()->with('error', __('message.some_thing_went_wrong'));
        }
    }

    public function pinDetailPage($id){
        try {

            $pin = PinMark::with([
                'user',
                'comments.user',
                'likes.user',
                'pinReports.reporter',
                'pinReports.reportedUser',
            ])
            ->withCount([
                'comments',
                'likes',
                'pinReports'
            ])
            ->find($id);

            if (!$pin) {
                return redirect()->back()->with('error', 'Pin not found.');
            }

            return view('admin.pin.detail', compact('pin'));

        } catch (\Exception $e) {

            Log::error(__METHOD__ . ' : ' . $e->getMessage());

            return redirect()->back()->with('error', __('message.some_thing_went_wrong'));
        }
    }
}