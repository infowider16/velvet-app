<?php



namespace App\Services;



use App\Contracts\Services\SocialLoginServiceInterface;

use App\Repositories\Eloquent\UserRepository;

use Illuminate\Support\Facades\Log;

class SocialLoginService implements SocialLoginServiceInterface

{

    protected $userRepo;



    public function __construct(UserRepository $userRepo)

    {

        $this->userRepo = $userRepo;

    }



    public function socialLogin(array $data)

    {

        try {

            // Find user by gmail_id OR google_id (not both)

            $user = $this->userRepo->getByWhere([

                'gmail_id' => $data['gmail_id']

            ], [], ['*'], [], [], 'first');



            if (!$user) {

                // Try to find by google_id if not found by gmail_id

                $user = $this->userRepo->getByWhere([

                    'google_id' => $data['google_id']

                ], [], ['*'], [], [], 'first');

            }



            if (!$user) {

                // Create new user if not found

                $user = $this->userRepo->create([

                    'gmail_id' => $data['gmail_id'],

                    'google_id' => $data['google_id'],

                    'is_active' => 1,
                    'registration_type' => 'social',

                ]);

            }



            // Check if user is blocked by admin
            
            if ($user->is_approve == 1) {

                throw new \Exception(__('message.account_blocked_contact_admin'));

            }



            $token = $user->createToken('API Token')->accessToken;

            $user->is_profile_completed = ((int)$user->is_active >= 7);

            $user->booster_expire_time = checkBoosterActive($user->id)['booster_expire_time'];

            return [

                'token' => $token,

                'user' => $user

            ];

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            throw new \Exception('Social login failed: ' . $e->getMessage());

        }

    }

}

