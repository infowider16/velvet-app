<?php



namespace App\Services;



use App\Repositories\Eloquent\{PinMarkCommentRepository,PinMarkRepository};

use App\Contracts\Repositories\PinRepositoryInterface;

use App\Contracts\Repositories\UserRepositoryInterface;

use App\Models\GhostManagement;

use Illuminate\Support\Facades\Log;

use Yajra\DataTables\Facades\DataTables;

use Carbon\Carbon;

use Illuminate\Validation\ValidationException;



use App\Services\UserRegisterService;



class PinMarkCommentService

{

    protected PinMarkCommentRepository $pinMarkCommentRepo;

    protected UserRepositoryInterface $userRepo;

    protected PinMarkRepository $pinMarkRepo;

    protected $userRegisterService;



    public function __construct(PinMarkCommentRepository $pinMarkCommentRepo, UserRepositoryInterface $userRepo,UserRegisterService $userRegisterService,PinMarkRepository $pinMarkRepo)

        {

        $this->pinMarkCommentRepo = $pinMarkCommentRepo;

        $this->userRepo = $userRepo;

        $this->userRegisterService = $userRegisterService;

        $this->PinMarkRepository = $pinMarkRepo;

    }



   public function storePinMarkComment(array $requestDatas)

        {

    try {

        $this->validateRequiredKeys($requestDatas);

        $userId = (int) $requestDatas['user_id'];

        $pinDetail=$this->PinMarkRepository->getOneData(['id'=>$requestDatas['pin_mark_id']],['user']);

        $swissNowFormatted = convertTimezone(

            Carbon::now(),

            null,

            'Y-m-d H:i:s'

        );



        $requestDatas['created_at']   = $swissNowFormatted;

        $requestDatas['commented_on'] = $swissNowFormatted;

        $requestDatas['total_like'] = 0;



        // Validate pin availability

        // $this->validatePinCount($userId);



        // Create MarkComment

        $pinMarkComment = $this->pinMarkCommentRepo->create($requestDatas);



        // Deduct pin count

        $this->updateDeductPinCount($userId);



        // Fetch user detail ONCE

        $userResponse = $this->userRegisterService->getUserDetail($userId);



        // Attach only user_info

        $pinMarkComment->user = $userResponse['data']['user_info'] ?? null;

        

        $sender   = $this->userRepo->find($userId);      // commenter

        $receiver = $pinDetail->user;    // post owner / receiver

        

        $title = __('message.new_comment_title');
        $body = $sender ? __('message.commented_on_post_by_user', ['name' => $sender->name]) : __('message.commented_on_post');


        // Extra payload for app handling

        $other = [

            'type'        => 'comment',

            'user_id'     => $userId,

            'screen_name' => 'post_detail', // or 'comment_list'

        ];

        

        // Send push

        if (!empty($receiver?->device_token) && $receiver->id!=$userId) {

            sendPushNotification(

                [$receiver->device_token],

                $title,

                $body,

                $other,

                [$receiver->id],'comments_on_pins'

            );

        }



        return $pinMarkComment;



    } catch (ValidationException $e) {

        throw $e;



    } catch (\Throwable $e) {

        dd($e);

        Log::error(

            __CLASS__ . '::' . __FUNCTION__,

            [

                'error' => $e->getMessage(),

                'trace' => $e->getTraceAsString(),

                'data'  => $requestDatas

            ]

        );



        return null;

    }

}



    /**

     * -----------------------

     * Private reusable helpers

     * -----------------------

     */



    private function validateRequiredKeys(array $data): void

        {

        foreach (['comment', 'user_id','pin_mark_id'] as $field) {

            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {

                throw ValidationException::withMessages([

                    $field => ucfirst(str_replace('_', ' ', $field)) . ' is required.',

                ]);

            }

        }

    }



//     private function validatePinCount(int $userId): void

//         {

//     $user = $this->userRepo->getOneData(['id' => $userId]);



//     if (!$user) {

//         throw ValidationException::withMessages([

//             'user_id' => 'User not found.',

//         ]);

//     }



//     // ❌ No pins → do not allow

//     if ((int) $user->pin_count <= 0) {

//         throw ValidationException::withMessages([

//             'pin_count' => 'You do not have enough pins to create this MarkComment.',

//         ]);

//     }

// }



    

    private function updateDeductPinCount(int $userId): int

        {

            $user = $this->userRepo->getOneData(['id' => $userId]);

        

            if (!$user) {

                throw ValidationException::withMessages([

                    'user_id' => 'User not found.',

                ]);

            }

        

            // Deduct 1 pin, minimum 0

            $newPinCount = max(0, ((int) $user->pin_count) - 1);

        

            $this->userRepo->update(

                ['id' => $userId],

                ['pin_count' => $newPinCount]

            );

        

            return $newPinCount;

        }



    private function getUserData( int $userId): array

        {

        $user = $this->userRepo->getOneData(['id' => $userId]);



        $data['user_data'] = $user;



        return $data;

    }

    

    

    public function fetchPinMarkComments(array $filters = [])

        {

    try {

        $markComment = $this->pinMarkCommentRepo->fetch($filters);



        $items = method_exists($markComment, 'getCollection')

            ? $markComment->getCollection()

            : collect($markComment);

        



        $items->transform(function ($markComment) {

            $user = $this->userRepo->getOneData([

                'id' => $markComment->user_id

            ]);



            if ($user) {

                $result = $this->userRegisterService->getUserDetail($user->id);



                $markComment->user = $result['data']['user_info'] ?? null;

            } else {

                $markComment->user = null;

            }



            return $markComment;

        });



        return $markComment;



    } catch (\Throwable $e) {

        Log::error(

            __CLASS__ . '::' . __FUNCTION__,

            ['error' => $e->getMessage(), 'filters' => $filters]

        );



        return [];

    }

}



    public function deletePinMarkComment(int $id): bool

        {

        try {

            return $this->pinMarkCommentRepo->softDeleteById($id);

    

        } catch (\Throwable $e) {

            Log::error(

                __CLASS__ . '::' . __FUNCTION__,

                ['error' => $e->getMessage(), 'id' => $id]

            );

    

            return false;

        }

    }



  

}

