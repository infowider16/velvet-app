<?php



namespace App\Services\Admin;



use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

use App\Repositories\Eloquent\{AdminRepository};

use App\Contracts\Services\AuthServiceInterface;

use App\Contracts\Repositories\AdminRepositoryInterface;

use App\Helpers\AuthHelper;

use App\Traits\UploadImageTrait;

use Illuminate\Support\Facades\File;

use Exception;

use App\Mail\DemoMail;

use Illuminate\Support\Facades\Mail;

use App\Services\BaseService;



class AuthServices extends BaseService implements AuthServiceInterface

{

    use UploadImageTrait;

    

    private $dataObject;

    private $adminrepository;



    public function __construct(AdminRepositoryInterface $adminrepository)

    {

        $this->dataObject = new \stdClass();

        $this->adminrepository = $adminrepository;

    }



    public function login($request)

    {

        try {

            $credentials = $request->only('email', 'password');



            if (adminAttempt($credentials)) {

                $request->session()->regenerate();

                return response()->json([

                    'status' => 1,

                    'message' => 'Login successful',

                    'data' => ['redirect' => route('admin.dashboard')]

                ], 200);

            }



            return response()->json([

                'status' => 0,

                'message' => 'Invalid credentials',

                'errors' => ['email' => ['Invalid credentials']]

            ], 422);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([

                'status' => 0,

                'message' => __('message.some_thing_went_wrong')

            ], 500);

        }

    }



    public function updatePassword($request)

    {

        try {

            $admin = getAdminUser();
            

            if (!$admin) {

                return $this->adminErrorResponse(__('message.user_not_authenticated'), [], [], 0, 401);

            }



            $allData['password'] = Hash::make($request->new_password);

            $data = $this->adminrepository->createOrUpdate(['id' => $admin->id], $allData);
            

            if ($data) {

                return $this->adminSuccessResponse([], __('message.password_changed'), 1, 200);

            }
            

            return $this->adminErrorResponse(__('message.an_error_occured'), [], [], 0, 500);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function update($request)

    {

        try {

            $admin = getAdminUser();

            if (!$admin) {

                return $this->adminErrorResponse(__('message.user_not_authenticated'), [], [], 0, 401);

            }



            $user = $this->adminrepository->getOne(['id' => $admin->id]);



            $allData['name'] = $request->name;

            $allData['email'] = $request->email;

           

            if ($request->hasFile('profile_image')) {

                $allData['profile_image'] = $this->uploadImage($request->file('profile_image'), 'profileImages');

            }



            $data = $this->adminrepository->createOrUpdate(['id' => $admin->id], $allData);



            if ($data) {

                if (isset($allData['profile_image'])) {

                    $this->handleOldImageDeletion($user);

                }

                return $this->adminSuccessResponse($data, __('message.profileUpdate'), 1, 200);

            } else {

                return $this->adminErrorResponse(__('message.an_error_occured'), [], [], 0, 500);

            }

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.an_error_occured'), [], [], 0, 500);

        }

    }



    public function forgetPassword($request)

    {

        try {

            $email = $request->email;



            if (!empty($email)) {

                $adminData = $this->adminrepository->getOne(['email' => $email]);

            }



            if ($adminData) {

                $randomPassword = mt_rand(100000, 999999);

                $update['password'] = Hash::make($randomPassword);

                

                $this->adminrepository->createOrUpdate(['id' => $adminData->id], ['password' => $update['password']]);



                $run = $this->sendPasswordResetEmail($email, $adminData->name, $randomPassword);



                if ($run) {

                    return $this->adminSuccessResponse([], __('message.forget_pass_success_msg'), 1, 200);

                } else {

                    return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

                }

            } else {

                return $this->adminErrorResponse(__('message.email_not_found'), [], [], 0, 404);

            }

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse(__('message.some_thing_went_wrong'), [], [], 0, 500);

        }

    }



    public function updateContent($request)

    {

        // This method seems to be for content management, not auth

        // You may want to move this to a separate ContentService

        try {

            $data = $request->all();

            // Implementation depends on your content repository

            return $this->errorResponse('Method not implemented for AuthService', 0);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->errorResponse(__('message.statusZero'), 0);

        }

    }



    public function getNotifications($request)

    {

        // This method seems to be for notifications, not auth

        // You may want to move this to a separate NotificationService

        try {

            return $this->errorResponse('Method not implemented for AuthService', 0);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 0);

        }

    }



    public function clearNotifications($request, $userdata)

    {

        // This method seems to be for notifications, not auth  

        // You may want to move this to a separate NotificationService

        try {

            return $this->errorResponse('Method not implemented for AuthService', 0);

        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 0);

        }

    }



    /**

     * Handle old image deletion

     */

    private function handleOldImageDeletion($user)

    {

        if ($user->profile_image) {

            $userPath = storage_path('app/public/');

            $oldImagePath = $userPath . ($user->profile_image);

            $protectedImagePath = $userPath . 'profileImages/user-avatar.jpg';

            if (File::exists($oldImagePath) && $oldImagePath !== $protectedImagePath) {

                File::delete($oldImagePath);

            }

        }

    }



    /**

     * Send password reset email

     */

    private function sendPasswordResetEmail($email, $name, $randomPassword)

    {

        if (!empty($email)) {

            try {

                // Check mail configuration first

                $mailConfig = config('mail.default');

               
                $body = __('message.greeting_message', ['name' => $name]);

                $body .= '<p>' . __('message.forgot_password_message') . '</p>';

                $body .= '<p>' . __('message.temporary_password_message', ['password' => $randomPassword]) . '</p>';



                $mailData = [

                    'subject' => __('message.forgot_password_subject'),

                    'email' => $email,

                    'body' => $body,

                    'password' => $randomPassword,

                ];

                // Send to user

                Mail::to($email)->send(new DemoMail($mailData));


                return true;

            } catch (\Exception $mailException) {

                Log::error('Mail sending failed: ' . $mailException->getMessage());

                Log::error('Mail exception trace: ' . $mailException->getTraceAsString());

                return false;

            }

        }

        Log::warning('Email is empty, cannot send mail');

        return false;

    }

}



