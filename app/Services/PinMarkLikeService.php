<?php



namespace App\Services;



use App\Models\PinMark;

use App\Repositories\Eloquent\PinMarkLikeRepository;

use App\Contracts\Repositories\UserRepositoryInterface;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

use Illuminate\Validation\ValidationException;

use App\Services\UserRegisterService;



class PinMarkLikeService

{

    protected PinMarkLikeRepository $pinMarkLikeRepo;

    protected UserRepositoryInterface $userRepo;

    protected $userRegisterService;



    public function __construct(PinMarkLikeRepository $pinMarkLikeRepo, UserRepositoryInterface $userRepo, UserRegisterService $userRegisterService)

    {

        $this->pinMarkLikeRepo = $pinMarkLikeRepo;

        $this->userRepo = $userRepo;

        $this->userRegisterService = $userRegisterService;
    }



    /**

     * Like / Unlike PinMark

     */

    public function toggleLike(array $data): array

    {

        $this->validateRequest($data);



        $pinMarkId = (int) $data['pin_mark_id'];

        $userId    = (int) $data['user_id'];



        $pinMark = PinMark::find($pinMarkId);


        if (!$pinMark) {

            throw ValidationException::withMessages([

                'pin_mark_id' => __('message.pin_mark_not_found'),

            ]);
        }



        DB::transaction(function () use ($pinMark, $pinMarkId, $userId) {



            $alreadyLiked = $this->pinMarkLikeRepo

                ->exists($pinMarkId, $userId);



            if ($alreadyLiked) {

                // UNLIKE

                $this->pinMarkLikeRepo->deleteLike($pinMarkId, $userId);

                $pinMark->decrement('total_like');
            } else {

                // LIKE

                $this->pinMarkLikeRepo->createLike($pinMarkId, $userId);

                $pinMark->increment('total_like');

                $sender   = $this->userRepo->find($userId);              // user who liked

                $receiver = $this->userRepo->find($pinMark->user_id);    // post owner


                $title = __('message.new_like_title', [], 'en');
                $body = $sender ? __('message.liked_post_by_user', ['name' => $sender->name], 'en') : __('message.liked_post', [], 'en');

                // Extra payload for app handling

                $other = [

                    'type'        => 'like',

                    'user_id'     => $userId,

                    'screen_name' => 'post_detail', // post screen

                ];



                // Send push

                if (!empty($receiver?->device_token)  && $receiver->id != $userId) {

                    sendPushNotification(

                        [$receiver->device_token],

                        $title,

                        $body,

                        $other,

                        [$receiver->id],
                        'likes_on_pins'

                    );
                }
            }
        });

        return [

            'is_liked'   => $this->pinMarkLikeRepo->exists($pinMarkId, $userId),

            'total_like' => $pinMark->fresh()->total_like,

        ];
    }



    /**

     * Validation

     */

    private function validateRequest(array $data): void

    {

        foreach (['pin_mark_id', 'user_id'] as $field) {

            if (!isset($data[$field]) || $data[$field] === '') {

                throw ValidationException::withMessages([

                    $field => ucfirst(str_replace('_', ' ', $field)) . ' is required.',

                ]);
            }
        }
    }

    /**

     * Fetch users who liked a pin mark

     */

    public function fetchLikedUsers(array $filters = [])

    {

        try {

            $this->validateRequestLike($filters);



            $likes = $this->pinMarkLikeRepo

                ->fetchByPinMarkId((int) $filters['pin_mark_id']);



            $items = method_exists($likes, 'getCollection')

                ? $likes->getCollection()

                : collect($likes);





            $items->transform(function ($likes) {



                $user = $this->userRepo->getOneData([

                    'id' => $likes->user_id

                ]);



                if ($user) {

                    $result = $this->userRegisterService->getUserDetail($user->id);



                    $likes->user = $result['data']['user_info'] ?? null;
                } else {

                    $likes->user = null;
                }





                return $likes;
            });



            return $likes;
        } catch (\Throwable $e) {

            Log::error(

                __CLASS__ . '::' . __FUNCTION__,

                [

                    'error' => $e->getMessage(),

                    'filters' => $filters

                ]

            );



            return [];
        }
    }











    private function validateRequestLike(array $data): void

    {

        if (empty($data['pin_mark_id'])) {

            throw ValidationException::withMessages([

                'pin_mark_id' => 'Pin mark id is required.',

            ]);
        }
    }
}
