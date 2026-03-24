<?php



namespace App\Http\Controllers\Admin;



use App\Http\Requests\Admin\{AdminLoginRequest, UpdatePasswordRequest, UpdateAdminProfileRequest};

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

use Exception;

use App\Contracts\Services\AuthServiceInterface;

use Illuminate\Support\Facades\Validator;



use App\Http\Controllers\BaseController;



class AuthController extends BaseController

{

    private $dataObject;

    private $authServices;



    public function __construct(AuthServiceInterface $authServices)

    {

        $this->dataObject = new \stdClass();

        $this->authServices = $authServices;

    }



    public function index()

    {

        try {

            return view('admin.login');

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return redirect()->route('admin.login')->withErrors(['error' => __('message.some_thing_went_wrong')]);

        }

    }



    public function login(AdminLoginRequest $request)

    {

        try {

            $result = $this->authServices->login($request);

            // The service already returns proper JSON response

            return $result;

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }





    public function update(UpdateAdminProfileRequest $request)

    {

        try {

            $result = $this->authServices->update($request);

            // The service now returns proper JSON response

            return $result;

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function changepassword()

    {

        try {

            return view('admin.change-password');

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return redirect()->route('admin.login')->withErrors(['error' => __('message.some_thing_went_wrong')]);

        }

    }



    public function updatePassword(UpdatePasswordRequest $request)

    {

        try {

            $result = $this->authServices->updatePassword($request);

            // The service now returns proper JSON response

            return $result;

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function forgotpassword()

    {

        try {

            if (Auth::guard('admin')->check()) {

                return redirect()->route('admin.dashboard');

            }

            return view('admin.forgot-password');

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return redirect()->route('admin.login')->withErrors(['error' => __('message.some_thing_went_wrong')]);

        }

    }



    public function sendForgotPasswordEmail(Request $request)

    {

        try {

            if (!empty($request->email)) {

                $rules = ['email' => 'required|email'];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {

                    return $this->adminErrorResponse(__('message.validation_failed'), [], $validator->errors(), 0, 422);

                }

            }



            $result = $this->authServices->forgetPassword($request);

            return $result;

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse(__('message.statusZero'), [], [], 0, 500);

        }

    }



    public function logout()

    {

        try {

            Auth::guard('admin')->logout();

            return redirect()->route('admin.login');

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return redirect()->route('admin.login')->withErrors(['error' => __('message.some_thing_went_wrong')]);

        }

    }



    public function getUserDetails()

    {

        try {

            $userDetails = getAdminDetails();

            return $this->adminSuccessResponse($userDetails, 'User details retrieved', 1, 200);

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function profile()

    {

        try {

            return view('admin.profile');

        } catch (Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return redirect()->route('admin.dashboard')->withErrors(['error' => __('message.some_thing_went_wrong')]);

        }

    }



    /**

     * Log errors consistently

     */

    private function logError($function, $exception)

    {

        Log::error("Error in " . __CLASS__ . "::" . $function . ": " . $exception->getMessage());

    }

}

