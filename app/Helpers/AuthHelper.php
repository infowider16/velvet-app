<?php



use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Session;
use App\Models\AppSettingModel;
use App\Models\BoostHistory;
use App\Models\NotificationSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;
use Google\Service\FirebaseCloudMessaging;
use GuzzleHttp\Client as HttpClient;


/**

 * Class AuthHelper

 * Helper functions for authentication and user context.

 */



/**

 * Get the currently authenticated admin user

 */

if (!function_exists('getAdminUser')) {

    function getAdminUser()

    {

        try {

            return Auth::guard('admin')->user();
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get admin user ID

 */

if (!function_exists('getAdminId')) {

    function getAdminId()

    {

        try {

            $user = getAdminUser();

            return $user ? $user->id : null;
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get admin user name

 */

if (!function_exists('getAdminName')) {

    function getAdminName()

    {

        try {

            $user = getAdminUser();

            return $user ? $user->name : null;
        } catch (\Throwable $e) {

            return null;
        }
    }
}


if (!function_exists('checkBoosterActive')) {

    function checkBoosterActive($userId)
    {
        try {
            $nowswiss=Carbon::now('Europe/Zurich');
            $checkbooster=BoostHistory::where('user_id',$userId);
            $boosterStatus=$checkbooster->where('end_date_time','>',$nowswiss->toDateTimeString())->first();
            if(isset($boosterStatus->id)){
                return [
                    'is_active' => true,
                    'booster_expire_time' => $boosterStatus->end_date_time
                ];
            }else{
                $user = User::where('id', $userId)->where('boost','!=',0)->first();

                if (!$user) {
                    return [
                        'is_active' => false,
                        'booster_expire_time' => null
                    ];
                }

                if ((int) $user->boost_count === 0) {
                    // boost_count == 0 → update these columns
                    $user->update([
                        'boost'           => 0,
                        'booster_ranking' => 0,
                    ]);
                } else {
                    // boost_count > 0 → update these columns
                    $user->update([
                        'booster_ranking' => 0,
                    ]);
                }
                return [
                    'is_active' => false,
                    'booster_expire_time' => null
                ];
            }
            return [
                'is_active' => false,
                'booster_expire_time' => null
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}



/**

 * Get admin user email

 */

if (!function_exists('getAdminEmail')) {

    function getAdminEmail()

    {

        try {

            $user = getAdminUser();

            return $user ? $user->email : null;
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Check if admin is authenticated

 */

if (!function_exists('isAdminLoggedIn')) {

    function isAdminLoggedIn()

    {

        try {

            return Auth::guard('admin')->check();
        } catch (\Throwable $e) {

            return false;
        }
    }
}



/**

 * Get all admin user details as array

 */

if (!function_exists('getAdminDetails')) {

    function getAdminDetails()

    {

        try {

            $user = getAdminUser();

            if (!$user) {

                return null;
            }



            return [

                'id' => $user->id,

                'name' => $user->name,

                'email' => $user->email,

                'created_at' => $user->created_at,

                'updated_at' => $user->updated_at,

                'is_logged_in' => true,

                'guard' => 'admin'

            ];
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get regular user (if you have regular users)

 */

if (!function_exists('getUser')) {

    function getUser()

    {

        try {

            return Auth::user();
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get regular user ID

 */

if (!function_exists('getUserId')) {

    function getUserId()

    {

        try {

            $user = getUser();

            return $user ? $user->id : null;
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Check if regular user is authenticated

 */

if (!function_exists('isUserLoggedIn')) {

    function isUserLoggedIn()

    {

        try {

            return Auth::check();
        } catch (\Throwable $e) {

            return false;
        }
    }
}



/**

 * Get session data for authenticated user

 */

if (!function_exists('getSessionData')) {

    function getSessionData($key = null)

    {

        try {

            if ($key) {

                return Session::get($key);
            }

            return Session::all();
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get user avatar/profile picture URL with proper protocol.

 */

if (!function_exists('getUserAvatar')) {

    function getUserAvatar()

    {

        try {

            $user = getAdminUser() ?: getUser();

            if ($user && isset($user->profile_image) && $user->profile_image) {

                // Use asset() to ensure correct protocol

                return asset($user->profile_image);
            }

            return asset('images/default-avatar.png'); // fallback avatar

        } catch (\Throwable $e) {

            return asset('images/default-avatar.png');
        }
    }
}



/**

 * Get user's last login time

 */

if (!function_exists('getLastLoginTime')) {

    function getLastLoginTime()

    {

        try {

            $user = getAdminUser() ?: getUser();

            if ($user && isset($user->last_login_at)) {

                return $user->last_login_at;
            }

            return null;
        } catch (\Throwable $e) {

            return null;
        }
    }
}



/**

 * Get complete user context for views

 */

if (!function_exists('getUserContext')) {

    function getUserContext()

    {

        try {

            return [

                'admin' => getAdminDetails(),

                'user' => getUser(),

                'is_admin_logged_in' => isAdminLoggedIn(),

                'is_user_logged_in' => isUserLoggedIn(),

                'avatar' => getUserAvatar(),

                'last_login' => getLastLoginTime()

            ];
        } catch (\Throwable $e) {

            return [];
        }
    }
}



/**

 * Attempt to authenticate as admin.

 *

 * @param array $credentials

 * @return bool

 */

if (!function_exists('adminAttempt')) {

    function adminAttempt(array $credentials, $guard = 'admin')

    {

        try {

            return Auth::guard($guard)->attempt($credentials);
        } catch (\Throwable $e) {

            return false;
        }
    }
}



if (!function_exists('isPhoneCodeNumberTaken')) {

    function isPhoneCodeNumberTaken($phoneCode, $phoneNumber)

    {



        return \App\Models\User::where('phone_code', $phoneCode)

            ->where('phone_number', $phoneNumber)

            ->exists();
    }
}
if (!function_exists('getAccessToken')) {
    function getAccessToken($project_id)
    {
        // Reminder: You must run "composer require google/apiclient" in your project root!
        if (!class_exists('\Google\Client')) {
            Log::error('Google API Client library not found. Please run "composer require google/apiclient".');
            return false;
        }
        // Replace with the path to your service account JSON file
        $serviceAccountPath = base_path('config/' . $project_id . '-service-account.json');
        if (empty($serviceAccountPath) || !file_exists($serviceAccountPath)) {
            return false;
        }

        try {
            // run "composer require google/apiclient" in terminal to get access token

            // Create a Google Client instance
            $client = new GoogleClient();

            // Set the authentication config from the service account file
            $client->setAuthConfig($serviceAccountPath);

            // Add the required scope for FCM messaging
            $client->addScope(FirebaseCloudMessaging::CLOUD_PLATFORM);

            // Attempt to fetch the access token
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithAssertion();
            }

            $accessToken = $client->getAccessToken();

            // Resolve the promise with the access token
            return $accessToken['access_token'];
        } catch (Exception $e) {
            Log::error("Error in Helper.getAccessToken(): catch " . $e->getMessage());
            return false;
        }
    }
}
if (!function_exists('sendPushNotification')) {
    function sendPushNotification($deviceIds, $title, $body, $data,$userIds=[],$type=null)
    {
        // if($type!=null){
        //     $users = NotificationSetting::whereIn('id', $userIds)->where('type',$type)->get();
        //     if(isset($users[0]->id) && $users[0]->type==0){
        //         return 0;
        //     }
        // }else{
        //     $users = User::whereIn('id', $userIds)->get(['id', 'push_notification_status']);
        //     if(isset($users[0]->id) && $users[0]->push_notification_status==0){
        //         return 0;
        //     }
        // }
        if (!empty($type) && !empty($userIds)) {
            $typeDisabled = NotificationSetting::whereIn('user_id', $userIds)
                ->where('type', $type)
                ->where('is_enabled', 0)
                ->exists();
        
            if ($typeDisabled) {
                return 0;
            }
        }
        
        
        
        $status = 0;

        try {
            $project_id = AppSettingModel::where('key', 'firebase_project_id')->value('value');
            $project_number = AppSettingModel::where('key', 'firebase_project_number')->value('value');
            if (empty($project_id) || empty($project_number)) {
                Log::error("Error in Helper.sendPushNotification(): project_id,project_number=empty");
                return $status;
            }

            $accessToken = getAccessToken($project_id);
            Log::info(json_encode($accessToken));
            if (empty($accessToken)) {
                Log::error("Error in Helper.sendPushNotification(): accesstoken=empty");
                return $status;
            }

            if (count($deviceIds) > 1) {

                $fields = [
                    "operation" => "create",
                    "notification_key_name" => uniqid(),
                    "registration_ids" => $deviceIds
                ];
                $headers = array(
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json',
                    'access_token_auth: true',
                    'project_id: ' . $project_number
                );


                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/notification');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

                $result = curl_exec($ch);
                $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if (curl_errno($ch)) {
                    Log::error("Error in Helper.sendPushNotification():fcm/notification Curl error: " . curl_error($ch));
                }
                curl_close($ch);

                if ($httpcode !== 200) {
                    Log::error("Error in Helper.sendPushNotification(): multiple device get token " . $result);
                    return $status;
                } else {
                    $res = json_decode($result);
                    $deviceToken = $res->notification_key;
                }
            } else {

                $deviceToken = $deviceIds[0];
            }

            if (empty($deviceToken) || empty($title)) {
                Log::error("Error in Helper.sendPushNotification(): devicetoken or title empty.");
                return $status;
            }
            if (is_string($deviceToken) && str_starts_with($deviceToken, '[')) {
                $decoded = json_decode($deviceToken, true);
                if (is_array($decoded)) {
                    $deviceToken = $decoded[0];
                }
            }
            $fields = [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => [
                        "body" => $body,
                        "title" => $title
                    ],
                    "data" => integer_to_string($data)
                ]
            ];
            // dd($fields);

            $headers = array(
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


            $result = curl_exec($ch);
            
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (curl_errno($ch)) {
                Log::error("Error in Helper.sendPushNotification():messages:send Curl error: " . curl_error($ch));
            }
            curl_close($ch);

            if ($httpcode !== 200) {
                Log::error("Error in Helper.sendPushNotification(): messages:send result " . $result);
                $status = 0;
            } else {
                // Log::error("Error in Helper.sendPushNotification(): result " . $result);
                $result = json_decode($result);
                // print_r($result);
                if (empty($result)) {
                    Log::error("Error in Helper.sendPushNotification(): messages:send result empty ");
                    $status = 0;
                } else {
                    $status = 1;
                }
                // dd($result);
            }
        } catch (Exception $e) {
            Log::error("Error in Helper.sendPushNotification(): catch " . $e->getMessage());
            throw $e;
        }

        return $status;
    }
}
function integer_to_string($data)
{
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = (string) $value;
        }
    } else {
        $data = (string) $data;
    }
    return $data;
}


/**

 * Get full image URL for a given path.

 */

if (!function_exists('getImageUrl')) {

    function getImageUrl($path)

    {

        if (!$path) return null;

        // Only prepend asset if not already a full URL

        if (filter_var($path, FILTER_VALIDATE_URL)) {

            return $path;

        }

        return asset('storage/' . ltrim($path, '/'));

    }

}



/**

 * Get array of image URLs from JSON or array.

 */

if (!function_exists('getImagesArray')) {

    function getImagesArray($images)

    {

        if (!$images) return [];

        $imgArr = is_string($images) ? json_decode($images, true) : $images;

        if (!is_array($imgArr)) return [];

        return array_map(function ($img) {

            if (filter_var($img, FILTER_VALIDATE_URL)) {

                return $img;

            }

            return asset('storage/' . ltrim($img, '/'));

        }, $imgArr);

    }

    if (!function_exists('convertTimezone')) {
        function convertTimezone(
            Carbon|DateTimeInterface|string|null $time,
            ?string $fromTz = null,
            ?string $format = null,
            string $toTz = 'Europe/Zurich'
        ): Carbon|string|null {

            // ✅ EARLY EXIT: null / empty
            if ($time === null || $time === '') {
                return null;
            }

            // ✅ EARLY EXIT: Carbon already in target timezone & no format
            if (
                $time instanceof Carbon &&
                $time->getTimezone()->getName() === $toTz &&
                $format === null
            ) {
                return $time;
            }

            // ---------- NORMAL FLOW ----------

            if ($time instanceof Carbon) {
                $carbon = $time->copy()->setTimezone($toTz);

            } elseif ($time instanceof DateTimeInterface) {
                $carbon = Carbon::instance($time)->setTimezone($toTz);

            } else {
                $carbon = Carbon::parse(
                    $time,
                    $fromTz ?? 'Europe/Zurich'
                )->setTimezone($toTz);
            }

            return $format
                ? $carbon->format($format)
                : $carbon;
        }
    }
    
    if (!function_exists('defaultTimezone')) {
        function defaultTimezone(){
            return 'Europe/Zurich';
        }
    }
    
    if (!function_exists('isPushNotificationEnabled')) {
        /**
         * Check if push notification is enabled for a user.
         *
         * Available Types:
         * - all_push (Master Toggle)
         * - new_messages
         * - comments_on_pins
         * - likes_on_pins
         * - friend_requests
         *
         * @param int $userId
         * @param string $type
         * @return bool
        */
        function isPushNotificationEnabled(int $userId, string $type): bool
        {
            
            $setting = NotificationSetting::firstOrCreate(
                ['user_id' => $userId, 'type' => $type],
                ['is_enabled' => true]
            );
    
            return (bool) $setting->is_enabled;
        }
    }

}