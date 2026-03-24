<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Contracts\Services\AdminUserServiceInterface;

class UserController extends BaseController

{

    protected AdminUserServiceInterface $userService;



    public function __construct(AdminUserServiceInterface $userService)

    {

        $this->userService = $userService;

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

}