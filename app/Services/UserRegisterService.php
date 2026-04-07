<?php







namespace App\Services;







use App\Repositories\Eloquent\UserRepository;

use App\Repositories\Eloquent\MessageRepository;

use Exception;

use RuntimeException;

use Throwable;

use Carbon\Carbon;

use App\Traits\UploadImageTrait;



use App\Contracts\Services\UserRegisterServiceInterface;

use Twilio\Rest\Client;

use Illuminate\Support\Arr;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

use Twilio\Exceptions\RestException;

use App\Models\NotificationSetting;

class UserRegisterService implements UserRegisterServiceInterface



{



    use UploadImageTrait;







    protected $userRepo;

    protected $messageRepo;

    public const TYPES = [

        'new_messages',

        'comments_on_pins',

        'likes_on_pins',

        'friend_requests',

    ];





    public function __construct(UserRepository $userRepo, MessageRepository $messageRepo)

    {



        $this->userRepo = $userRepo;

        $this->messageRepo = $messageRepo;

    }















    private function generateOtp($length = 6)



    {



        try {



            $min = (int) str_pad('1', $length, '0');



            $max = (int) str_pad('9', $length, '9');



            return random_int($min, $max);

        } catch (\Exception $e) {



            // Log error and return a default OTP



            error_log("OTP generation failed: " . $e->getMessage());



            return 123456; // Fallback OTP for development



        }

    }











    private function updateUser($userId, array $data, $isActive = null)



    {



        try {



            if ($isActive !== null) {



                $data['is_active'] = $isActive;

            }







            return $this->userRepo->updateOrCreate(



                ['id' => $userId],



                $data



            );

        } catch (\Exception $e) {



            error_log("User update failed: " . $e->getMessage());



            throw new Exception(__('message.failed_to_update_user_data'));

        }

    }

    

    public function getNotification($userId)

    {

        try {

            $existing = NotificationSetting::where('user_id', $userId)

            ->pluck('is_enabled', 'type')

            ->toArray();



            $data= collect(self::TYPES)

                ->map(fn ($type) => [

                    'type'       => $type,

                    'is_enabled' => (bool) ($existing[$type] ?? false),

                ])

                ->values()

                ->toArray();

            return [

                'data' => $data,

                'message' => __('message.all_notification')

            ];

        } catch (\Exception $e) {

            error_log("User update failed: " . $e->getMessage());

            throw new Exception(__('message.failed_to_update_user_data'));

        }

    }

    

    public function bulkUpsert(int $userId, array $settings)

    {

        $now = now();



        $rows = collect($settings)

            ->unique('type') // if request has duplicate types, keep first

            ->map(fn ($row) => [

                'user_id'     => $userId,

                'type'        => $row['type'],

                'is_enabled'  => (bool) $row['is_enabled'],

                'created_at'  => $now,

                'updated_at'  => $now,

            ])

            ->values()

            ->all();



        if (empty($rows)) {

            return collect();

        }



        DB::table('notification_settings')->upsert(

            $rows,

            ['user_id', 'type'],          // unique keys

            ['is_enabled', 'updated_at']  // update columns

        );



        $types = array_column($rows, 'type');



        return NotificationSetting::query()

            ->where('user_id', $userId)

            ->whereIn('type', $types)

            ->get(['type', 'is_enabled']);

    }

    



    public function register(array $data)

    {

        try {

            $otp = $this->generateOtp(6);

            $expiredAt = now()->addMinutes(10);

    

            $existingUser = $this->userRepo->getOneData([

                'phone_code' => $data['phone_code'],

                'phone_number' => $data['phone_number']

            ]);

    

            $userData = [

                'phone_code' => $data['phone_code'],

                'phone_number' => $data['phone_number'],

                'country_code' => $data['country_code'] ?? null,

                'registration_type' => 'phone',

                'otp' => $otp,

                'expired_at' => $expiredAt

            ];

    

            if (!$existingUser) {

                $userData['is_active'] = 0;

            }

    

            $user = $this->userRepo->updateOrCreate(

                [

                    'phone_code' => $data['phone_code'],

                    'phone_number' => $data['phone_number']

                ],

                $userData

            );

    

            if ($user->is_approve == 1) {

                throw ValidationException::withMessages([

                    'account' => [__('message.account_blocked_contact_admin')]

                ]);

            }

            if($data['phone_code']!=91){

                $receiverNumber = "+" . $data['phone_code'] . $data['phone_number'];

                $message = __('message.otp_login_registration', [

                    'otp' => $user->otp

                ]);

        

                $account_sid = config('services.twilio.account_sid');

                $auth_token = config('services.twilio.auth_token');

                $twilio_number = config('services.twilio.from');

        

                $client = new Client($account_sid, $auth_token);

                $client->messages->create($receiverNumber, [

                    'from' => $twilio_number,

                    'body' => $message

                ]);

            }

            return [

                'data' => [

                    'otp' => config('app.debug') ? $user->otp : null,

                    'user_id' => $user->id,

                    'is_active' => $user->is_active,

                    'expired_at' => $expiredAt,

                    'country_code' => $user->country_code,

                    'registration_type' => $user->registration_type

                ],

                'message' => __('message.otp_sent_to_phone')

            ];

        } catch (RestException $e) {

            // ✅ EXACT LOG FORMAT (unchanged)

            Log::error('Register OTP failed', [

                'phone_code' => $data['phone_code'] ?? null,

                'phone_number' => $data['phone_number'] ?? null,

                'error' => $e->getMessage(),

                'class' => get_class($e),

            ]);

    

           if ($e->getCode() == 21211) {

                throw ValidationException::withMessages([

                    'phone_number' => [__('message.invalid_phone_number')]

                ]);

            }



            if ($e->getCode() == 21614) {

                throw ValidationException::withMessages([

                    'phone_number' => [__('message.phone_number_cannot_receive_sms')]

                ]);

            }



            throw new \RuntimeException(__('message.failed_to_send_otp'));



        } catch (\Throwable $e) {

    

            // ✅ EXACT LOG FORMAT (unchanged)

            Log::error('Register OTP failed', [

                'phone_code' => $data['phone_code'] ?? null,

                'phone_number' => $data['phone_number'] ?? null,

                'error' => $e->getMessage(),

                'class' => get_class($e),

            ]);

    

           throw $e instanceof ValidationException

            ? $e

            : new \RuntimeException(__('message.registration_failed_try_later'));

        }

    }





    public function verifyOtp(array $data)



    {



        try {



            $user = $this->userRepo->getByWhere(



                $data,



                [],

                ['*'],

                ['transactions'],

                [],

                'first'



            );





            // Ensure user exists



            if (!$user) {



                throw new Exception(__('message.invalid_otp_or_user_not_found'));

            }







            // Check if user is blocked by admin



            if ($user && $user->is_approve == 1) {



                throw new Exception(__('message.account_blocked_contact_admin'));

            }







            if (empty($user->expired_at) || now()->greaterThan($user->expired_at)) {



                throw new Exception(__('message.otp_expired_request_new'));

            }







            // Only set is_active to 1 if user is verifying for first time (is_active = 0)

            // Otherwise keep the existing is_active value

            $newIsActive = ($user->is_active == 0) ? 1 : $user->is_active;



            $user = $this->updateUser($user->id, [], $newIsActive);







            $token = $user->createToken('API Token')->accessToken;











            // Prepare user info with extra fields



            $userInfo = $user->toArray();







            $userInfo['is_profile_completed'] = ((int)$user->is_active >= 7);

            $userInfo['transactions'] = $user->transactions ?? null;

            $userInfo['booster_expire_time'] = checkBoosterActive($user->id)['booster_expire_time'];





            return [



                'data' => [



                    'token' => $token,



                    'user_info' => $userInfo



                ],



                'message' => __('message.phone_verified_successfully')



            ];

        } catch (Exception $e) {



            return [



                'success' => false,



                'message' => $e->getMessage(),



                'error_code' => __('message.invalid_otp'),



                'data' => null



            ];

        }

    }







    public function completeProfile(array $data)



    {





        try {



            $user = $this->updateUser($data['user_id'], [



                'date_of_birth' => $data['date_of_birth'],



                'name' => $data['name'],



                'gender' => $data['gender'],



                'interest_id' => $data['interest_id'],



                'about_me' => $data['about_me'] ?? null,

                'images' => $data['images'] ?? null,





            ], 7);



            $userInfo = $user->toArray();







            $userInfo['is_profile_completed'] = ((int)$user->is_active >= 7);



            return [



                'data' => [



                    'user_id' => $user->id,



                    'is_active' => $user->is_active,

                    'user_info' => $userInfo



                ],



                'message' => __('message.profile_completed_successfully')



            ];

        } catch (Exception $e) {



            throw new Exception('Profile completion failed: ' . $e->getMessage());

        }

    }







    public function uploadImages(array $images)



    {



        try {



            // $user = $this->userRepo->getByWhere(['id' => $userId], [], ['*'], [], [], 'first');



            // if (!$user) throw new Exception(__('message.user_not_found'));







            // // Delete old images if exist



            // if (!empty($user->images)) {



            //     $oldImages = json_decode($user->images, true);



            //     if (is_array($oldImages) && count($oldImages)) {



            //         $this->deleteMediaFiles($oldImages);

            //     }

            // }







            $imagePaths = $this->uploadMediaFiles($images, 'user_images');







            // $user = $this->updateUser($userId, [



            //     'images' => json_encode($imagePaths)



            // ], 3);







            return [



                // 'user_id' => $user->id,



                'paths' => $imagePaths,



                // 'is_active' => $user->is_active



            ];

        } catch (Exception $e) {



            throw new Exception('Image upload failed: ' . $e->getMessage());

        }

    }



    public function uploadSingleImage($image)

    {

        try {



            // call trait method uploadImage($image, $path)

            $imagePath = $this->uploadImage($image, 'user_images');



            return [

                'path' => $imagePath

            ];

        } catch (Exception $e) {

            throw new Exception('Single image upload failed: ' . $e->getMessage());

        }

    }





    public function updateLocationConsent($userId, $locationConsent)



    {



        try {



            $user = $this->updateUser($userId, [



                'location_consent' => $locationConsent



            ], 4);

            return [
                'user_id' => $user->id,
                'is_delete' => $user->is_delete ?? 0,
                'location_consent' => $user->location_consent,
                'is_active' => $user->is_active
            ];

        } catch (Exception $e) {



            throw new Exception('Location consent update failed: ' . $e->getMessage());

        }

    }







    public function addLocation($userId, $location, $latitude, $longitude)



    {



        try {



            $user = $this->updateUser($userId, [



                'location' => $location,



                'lat' => $latitude,



                'lng' => $longitude



            ], 5);







            return [



                'user_id' => $user->id,

                'is_delete' => $user->is_delete ?? 0,



                'location' => $user->location,



                'lat' => $user->lat,



                'lng' => $user->lng,



                'is_active' => $user->is_active



            ];

        } catch (Exception $e) {



            throw new Exception('Location update failed: ' . $e->getMessage());

        }

    }



    private function sendOtpWithTwilio($phoneNumber, $otp)

    {

        try {

            $sid = env('TWILIO_SID');

            $token = env('TWILIO_AUTH_TOKEN');

            $from = env('TWILIO_PHONE');



            $client = new Client($sid, $token);



            $message = __('message.verification_code_message', ['otp' => $otp]);



            $client->messages->create($phoneNumber, [

                'from' => $from,

                'body' => $message,

            ]);



            return true;

        } catch (\Exception $e) {

            \Log::error("Twilio OTP send failed: " . $e->getMessage());

            return false;

        }

    }



    public function getUserDetail($userId)

    {

        try {

            $this->updatePlanExpiryStatuses();

            $user = $this->userRepo->getOneData(

                ['id' => $userId]

            );



            if (!$user) {

                throw new Exception(__('message.user_not_found'));

            }



            // Profile considered complete when is_active >= 7 (aligns with completeProfile)

            $user->is_profile_completed = ((int)$user->is_active >= 7);

            $user->interest_id = json_decode($user->interest_id, true);

            $user->images = json_decode($user->images, true);

            $user->booster_expire_time = checkBoosterActive($userId)['booster_expire_time'];



            return [

                'data' => [

                    'user_info' => $user,

                ],

                'message' => __('message.user_details_fetched_successfully')

            ];

        } catch (Exception $e) {

            throw new Exception('Failed to fetch user details: ' . $e->getMessage());

        }

    }

    

    public function deleteUserDetail(int $userId)

    {

        try {

            return DB::transaction(function () use ($userId) {

    

                $updated = DB::table('users')

                    ->where('id', $userId)

                    ->where('is_delete', 0)

                    ->update([

                        'is_delete'                 => 1,

                        'booster_ranking'           => 0,

                        'name'                      => null,

                        'phone_code'                => null,

                        'phone_number'              => null,

                        'registration_type'         => null,

                        'country_code'              => null,

                        'is_approve'                => 0,

                        'is_active'                 => 0,

                        'date_of_birth'             => null,

                        'gender'                    => null,

                        'interest_id'               => null,

                        'images'                    => null,

                        'about_me'                  => null,

                        'location'                  => null,

                        'lat'                       => null,

                        'lng'                       => null,

                        'location_consent'          => 0,

                        'city'                      => null,

                        'expired_at'                => null,

                        'otp'                       => null,

                        'gmail_id'                  => null,

                        'push_notification_status'  => null,

                        'device_token'              => null,

                        'google_id'                 => null,

                        'online_status'             => null,

                        'remember_token'            => null,

                        'last_seen_at'              => null,

                        'ghost'                     => 0,

                        'boost'                     => 0,

                        'boost_count'               => 0,

                        'pin_count'                 => 0,

                        'pin_transaction_id'        => 0,

                        'gost_expire'               => null,

                        'updated_at'                => Carbon::now(),

                    ]);

                DB::table('groups')->where('created_by', $userId)->delete();

                DB::table('pin_marks')->where('user_id', $userId)->delete();

                if ($updated === 0) {

                    throw new RuntimeException(__('message.user_not_found_or_already_deleted'));

                }

    

                return [

                    'data' => [],

                    'message' => __('message.user_details_deleted_successfully')

                ];

            });

        } catch (Throwable $e) {

            throw new RuntimeException('Failed to delete user details: ' . $e->getMessage(), 0, $e);

        }

    }



    private function updatePlanExpiryStatuses()

    {

        try {

            $swissNowFormatted = convertTimezone(

                Carbon::now(),

                null,

                'Y-m-d H:i:s'

            );

            DB::table('users')

                ->join('transactions', 'transactions.id', '=', 'users.ghost')

                ->where('users.ghost', '!=', 0)

                ->where('transactions.end_time', '<', $swissNowFormatted)

                ->update([

                    'users.ghost' => 0,

                    'users.updated_at' => $swissNowFormatted,

                ]);



        } catch (\Exception $e) {

            \Log::error('Failed to update plan expiry statuses: ' . $e->getMessage());

        }

    }





    public function UserDetail($userId, $currentUserId)

    {

        try {

            $user = $this->userRepo->getByWhere(

                ['id' => $userId],

                [],

                ['*'],

                ['pendingSentRequests', 'pendingReceivedRequests', 'acceptedFriendships', 'sentFriendRequests', 'receivedFriendRequests', 'blocks', 'blockedBy'],

                [],

                'first'

            );



            if (!$user) {

                throw new Exception(__('message.user_not_found'));

            }



            $friendStatus = $this->determineUserFriendStatus($user, $currentUserId);



            // Determine block_status (1 if current user blocked this user, 0 otherwise)

            $blockStatus = $user->blockedBy && $user->blockedBy->contains('blocker_id', $currentUserId) ? 1 : 0;



            // Profile considered complete when is_active >= 7 (aligns with completeProfile)

            $user->is_profile_completed = ((int)$user->is_active >= 7);

            $user->interest_id = json_decode($user->interest_id, true);

            $user->images = json_decode($user->images, true);



            $user->friend_status = $friendStatus;

            $user->block_status = $blockStatus;



            // Add notification_status for 1-to-1 chat

            $notificationStatus = app(\App\Repositories\Eloquent\MessageRepository::class)

                ->getNotificationStatus($currentUserId, $userId) ? 1 : 0;

            $user->notification_status = $notificationStatus;



            return [

                'data' => [

                    'user_info' => $user,

                ],

                'message' => __('message.user_details_fetched_successfully')

            ];

        } catch (Exception $e) {

            throw new Exception('Failed to fetch user details: ' . $e->getMessage());

        }

    }

    public function editProfile($userId, array $data)

    {

        try {





            $allowedFields = [

                'date_of_birth',

                'name',

                'gender',

                'interest_id',

                'about_me',

                'images',

                'push_notification_status',

                'phone_code',

                'phone_number',

                'country_code',

                'location',

                'lat',

                'lng',

            ];



            // Filter out only the fields that are actually sent in the request

            $updateData = Arr::only($data, $allowedFields);

            $user = $this->updateUser($userId, $updateData);



            $userInfo = $user->toArray();







            $userInfo['is_profile_completed'] = ((int)$user->is_active >= 7);



            return [



                'data' => [



                    'user_id' => $user->id,

                    'is_active' => $user->is_active,

                    'user_info' => $userInfo

                ],

                'message' => __('message.profile_updated_successfully')



            ];

        } catch (Exception $e) {



            throw new Exception('editProfile failed: ' . $e->getMessage());

        }

    }



    public function storeDeviceToken($userId, $deviceToken)

    {

        try {

            $user = $this->updateUser($userId, [

                'device_token' => $deviceToken

            ]);



            return [

                'user_id' => $user->id,

                'device_token' => $user->device_token

            ];

        } catch (Exception $e) {

            throw new Exception('Storing device token failed: ' . $e->getMessage());

        }

    }



    public function updateLatLng($userId, $latitude, $longitude, $country_code, $city)

    {

        try {

            $user = $this->updateUser($userId, [

                'lat' => $latitude,

                'lng' => $longitude,

                'country_code' => $country_code,

                'city' => $city,

            ]);



            return [

                'user_id' => $user->id,

                'lat' => $user->lat,

                'lng' => $user->lng,

                'city' => $user->city,

                'country_code' => $user->country_code,

            ];

        } catch (Exception $e) {

            throw new Exception('Updating latitude and longitude failed: ' . $e->getMessage());

        }

    }



    public function getIntervalSettings($userId)

    {

        try {

            // $user = $this->userRepo->getOneData(

            //     ['id' => $userId]

            // );



            $user = $this->userRepo->getByWhere(

                ['id' => $userId],

                [],

                ['*'],

                ['pendingSentRequests', 'pendingReceivedRequests', 'acceptedFriendships', 'sentFriendRequests', 'receivedFriendRequests', 'blocks', 'blockedBy'],

                [],

                'first'

            );



            $data['last_seen_at'] = now()->format('Y-m-d H:i:s');



            $updatelast = $this->userRepo->updateOrCreate(

                ['id' => $userId, 'ghost' => 0],

                $data

            );



            if (!$user) {

                throw new Exception(__('message.user_not_found'));

            }



            // Get unread notification count dynamically

            $unreadNotificationCount = $this->userRepo->getUnreadNotificationCount($userId);



            // --- DYNAMIC UNREAD MESSAGE COUNT ---

            // Count all messages where receiver_id = $userId and read_at is null

            $unreadMessageCount = $this->messageRepo->getByWhere(

                [

                    ['receiver_id', '=', $userId],

                    ['read_at', '=', null]

                ],

                [],

                ['*'],

                [],

                [],

                'count'

            );



            return [

                'unread_notification' => $unreadNotificationCount,

                'unread_message' => $unreadMessageCount,

                'pending_friend_request' => count($user->pendingReceivedRequests),

                'push_notification_status' => $user->push_notification_status,

                'is_approve' => $user->is_approve,

                'last_seen_at' => $user->last_seen_at,

            ];

        } catch (Exception $e) {

            throw new Exception('Fetching interval failed: ' . $e->getMessage());

        }

    }



    private function determineFriendStatus($user, $currentUserId)

    {



        // Check if they are friends (accepted friendship)

        // acceptedFriendships contains Friendship models where current user can be either user_id or friend_id

        $isFriend = $user->acceptedFriendships->contains(function ($friendship) use ($currentUserId) {

            return $friendship->user_id == $currentUserId || $friendship->friend_id == $currentUserId;

        });



        if ($isFriend) {

            return 2;

        }



        // Check if there's a pending request

        // pendingReceivedRequests: where user received request from currentUserId

        $hasPendingReceived = $user->pendingReceivedRequests->contains(function ($friendship) use ($currentUserId) {

            return 1;

        });



        // pendingSentRequests: where user sent request to currentUserId

        $hasPendingSent = $user->pendingSentRequests->contains(function ($friendship) use ($currentUserId) {

            return 3;

        });



        if ($hasPendingSent) {

            return 3;

        }

        if ($hasPendingReceived) {

            return 1;

        }



        // No relationship

        return 0;

    }



    // New method specifically for UserDetail to check friendship in both directions

    private function determineUserFriendStatus($user, $currentUserId)

    {

        // Check if they are friends (accepted friendship in EITHER direction)

        // Check in acceptedFriendships where current user is user_id

        $isFriendAsUser = $user->acceptedFriendships->contains(function ($friendship) use ($currentUserId, $user) {

            return $friendship->user_id == $user->id && $friendship->friend_id == $currentUserId;

        });



        // Check in sentFriendRequests where user_id is currentUserId and friend_id is this user

        $isFriendAsFriend = $user->receivedFriendRequests->contains(function ($friendship) use ($currentUserId) {

            return $friendship->user_id == $currentUserId && $friendship->status == 'accepted';

        });



        if ($isFriendAsUser || $isFriendAsFriend) {

            return 2; // Friends

        }



        // Check if current user sent a pending request to this user

        $currentUserSentRequest = $user->receivedFriendRequests->contains(function ($friendship) use ($currentUserId) {

            return $friendship->user_id == $currentUserId && $friendship->status == 'pending';

        });



        if ($currentUserSentRequest) {

            return 1; // Current user sent request (pending)

        }



        // Check if this user sent a pending request to current user

        $userSentRequest = $user->sentFriendRequests->contains(function ($friendship) use ($currentUserId) {

            return $friendship->friend_id == $currentUserId && $friendship->status == 'pending';

        });



        if ($userSentRequest) {

            return 3; // This user sent request to current user (pending)

        }



        // No relationship

        return 0;

    }

}

