<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\BaseController;

use App\Http\Requests\Api\{RequestOtpRequest, VerifyOtpRequest, CompleteProfileRequest, UploadImagesRequest, AddLocationRequest, UpdateLocationConsentRequest, UpdateProfileRequest};

use App\Services\UserRegisterService;

use Exception;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;



class UserRegisterController extends BaseController

{

    protected $userRegisterService;



    public function __construct(UserRegisterService $userRegisterService)

    {

        $this->userRegisterService = $userRegisterService;
    }





    public function register(RequestOtpRequest $request)

    {

        try {

            $result = $this->userRegisterService->register($request->validated());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }



    public function verifyOtp(VerifyOtpRequest $request)

    {

        try {

            $result = $this->userRegisterService->verifyOtp($request->validated());

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }



    public function completeProfile(CompleteProfileRequest $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $data = $request->validated();

            $data['user_id'] = $user->id; // Inject user_id from token

            $result = $this->userRegisterService->completeProfile($data);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }




    public function uploadImages(UploadImagesRequest $request)

    {

        try {

            //     $user = $this->getAuthenticatedUserOrError($request);

            //    if ($user instanceof JsonResponse) {

            //         return $user;

            //     }



            $result = $this->userRegisterService->uploadImages($request->file('images'));



            return $this->sendResponse($result, __('message.images_uploaded_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function uploadImage(Request $request)

    {

        try {


            $result = $this->userRegisterService->uploadSingleImage($request->file('image'));



            return $this->sendResponse($result, __('message.image_uploaded_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }


    public function updateLocationConsent(UpdateLocationConsentRequest $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }



            $result = $this->userRegisterService->updateLocationConsent($user->id, $request->input('location_consent'));



            return $this->sendResponse($result, __('message.location_consent_updated_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }



    public function addLocation(AddLocationRequest $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }



            $data = $request->validated();

            $result = $this->userRegisterService->addLocation(

                $user->id,

                $data['location'],

                $data['latitude'],

                $data['longitude']

            );



            return $this->sendResponse($result, __('message.location_updated_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }



    public function getUserDetail(Request $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $result = $this->userRegisterService->getUserDetail($user->id);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function deleteUserDetail(Request $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $result = $this->userRegisterService->deleteUserDetail($user->id);

            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    public function userDetail(Request $request)
    {

        try {
            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $result = $this->userRegisterService->UserDetail($request->id,$user->id);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function deviceToken(Request $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }
            $token = $request->input('device_token');
            $result = $this->userRegisterService->storeDeviceToken($user->id, $token);
            return $this->sendResponse($result, __('message.device_token_stored_successfully'));
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getInterval(Request $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $result = $this->userRegisterService->getIntervalSettings($user->id);

            return $this->sendResponse($result, __('message.interval_settings_retrieved_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function editProfile(UpdateProfileRequest $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $result = $this->userRegisterService->editProfile($user->id, $request->all());



            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function getNotificationSetting(Request $request)
    {
        try {
            $user = $this->getAuthenticatedUserOrError($request);
            $result = $this->userRegisterService->getNotification($user->id);
            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
    
    public function postNotificationSettings(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $validated = $request->validate([
                'settings'              => ['required', 'array', 'min:1'],
                'settings.*.type'       => ['required', 'string', Rule::in(userRegisterService::TYPES)],
                'settings.*.is_enabled' => ['required', 'boolean'],
            ]);
    
            $saved = $this->userRegisterService->bulkUpsert($userId, $validated['settings']);
    
            return $this->sendResponse($saved->map(fn ($s) => [
                    'type'       => $s->type,
                    'is_enabled' => (bool) $s->is_enabled,
                ])->values(), __('message.notification_settings_saved'));
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
    
            return $this->sendError($e->getMessage(), [], 500);
        }
    }    
    


    public function updateLatLng(Request $request)

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $latitude = $request->input('latitude');

            $longitude = $request->input('longitude');
            $country_code = $request->input('country_code');
            $city = $request->input('city');

            $result = $this->userRegisterService->updateLatLng($user->id, $latitude, $longitude, $country_code, $city);

            return $this->sendResponse($result, __('message.location_updated_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
