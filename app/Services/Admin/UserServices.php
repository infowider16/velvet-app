<?php



namespace App\Services\Admin;



use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AdminUserServiceInterface;
use App\Services\BaseService;
use App\Traits\UploadImageTrait;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\DB;



class UserServices extends BaseService implements AdminUserServiceInterface
{
    use UploadImageTrait;

    protected UserRepositoryInterface $userRepository;



    public function __construct(

        UserRepositoryInterface $userRepository,

    ) {

        $this->userRepository = $userRepository;

    }



    public function getUserListDataTable()

    {

        try {

            return $this->handleDataTableCall(function () {

                $users = $this->userRepository->getByWhere(['is_delete' => 0], ['id' => 'desc'], ['*'], [], [], 'get');



                return DataTables::of($users)

                    ->addIndexColumn()

                    ->addColumn('username', function ($row) {

                        return $row->name ?: '-';

                    })

                    ->addColumn('phone', function ($row) {

                        return ($row->phone_code ? '+' . $row->phone_code . ' ' : '') . ($row->phone_number ?? '-');

                    })

                    ->editColumn('status', function ($row) {

                        return $row->is_approve == 0

                            ? '<span class="badge bg-success">Unblocked</span>'

                            : '<span class="badge bg-danger">Blocked</span>';

                    })

                    ->addColumn('created_at', function ($row) {

                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';

                    })

                    ->addColumn('action', function ($row) {

                        $isBlocked = $row->is_approve == 1;

                        $toggleStatus = $isBlocked ? 0 : 1;

                        $btnClass = $isBlocked ? 'btn-success' : 'btn-danger';

                        $btnText = $isBlocked ? 'Unblock' : 'Block';

                        $viewUrl = route('admin.user.show', $row->id);



                        $viewBtn = '<a href="' . $viewUrl . '" class="btn btn-sm btn-info mr-1">View</a>';

                        $statusBtn = '<button class="btn btn-sm toggle-status mr-1 ' . $btnClass . '" 

                            data-id="' . $row->id . '" 

                            data-status="' . $toggleStatus . '">' . $btnText . '</button>';

                        // Add change number button

                        $changeNumberBtn = '<button class="btn btn-sm btn-warning change-number mr-1" 

                            data-id="' . $row->id . '" 

                            data-phone-code="' . $row->phone_code . '" 

                            data-phone-number="' . $row->phone_number . '" 

                            data-country-code="' . $row->country_code . '">Change Number</button>';

                        // Add delete button

                        $deleteBtn = '<button class="btn btn-sm btn-danger delete-user mr-1" data-id="' . $row->id . '">Delete</button>';



                        return $viewBtn . $statusBtn . $changeNumberBtn . $deleteBtn;

                    })

                    ->rawColumns(['status', 'action'])

                    ->make(true);

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse('Something went wrong while fetching user list', [], [], 0, 500);

        }

    }



    public function toggleUserStatus(Request $request)

    {

        try {

            return $this->handleServiceCall(function () use ($request) {

                $user = $this->userRepository->find($request->user_id);
              

                if (!$user) {

                    return ['status' => false, 'message' => __('message.user_not_found')];

                }



                $data = $this->userRepository->update(['id' => $request->user_id], ['is_approve' => $request->status]);

                $message = $request->status == 1 ? __('message.block') : __('message.unblock');



                return $data 

                    ? ['status' => true, 'message' => $message]

                    : ['status' => false, 'message' => __('message.some_thing_went_wrong')];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while toggling user status'];

        }

    }



    public function getUserDetail($id)

    {

        try{

        return $this->handleServiceCall(function () use ($id) {

            return $this->userRepository->getByWhere(['id' => $id], [], ['*'], [], [], 'first');

        }, null); // Return null on error

         } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->errorResponse('Something went wrong while fetching user details');

        }

    }



    public function uploadUserImage($request)
    {
        return $this->handleServiceCall(function () use ($request) {
            $user = $this->userRepository->find($request->user_id);

            if (!$user) {
                return ['status' => false, 'message' => __('message.user_not_found')];
            }

            if (!$request->hasFile('image')) {
                return ['status' => false, 'message' => 'No image was uploaded'];
            }

            $imagePath = $this->uploadImage($request->file('image'), 'user_images');

            $images = [];
            if (!empty($user->images)) {
                $images = is_array($user->images) ? $user->images : json_decode($user->images, true);
            }

            if (!is_array($images)) {
                $images = [];
            }

            $images[] = $imagePath;
            $user->images = json_encode(array_values($images));
            $user->save();

            return ['status' => true, 'message' => 'Image uploaded successfully'];
        });
    }

    public function deleteUser($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                $user = $this->userRepository->find($id);

                if (!$user || $user->is_delete == 1) {
                    return [
                        'status' => false,
                        'message' => __('message.user_not_found_or_already_deleted')
                    ];
                }

                $this->userRepository->update(['id' => $id], ['is_delete' => 1]);

                DB::table('groups')->where('created_by', $id)->delete();
                DB::table('pin_marks')->where('user_id', $id)->delete();

                return ['status' => true, 'message' => __('message.user_details_deleted_successfully')];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while deleting user'];

        }

    }



    public function updateUserPhone($id,$phoneNumber)

    {

        try {

            return $this->handleServiceCall(function () use ($id, $phoneNumber) {

                $user = $this->userRepository->find($id);

                if (!$user) {

                    return ['status' => false, 'message' => __('message.user_not_found')];

                }


                $updated = $this->userRepository->update(['id' => $id], [

               

                    'phone_number' => $phoneNumber,

                  

                ]);



                if ($updated) {

                    return ['status' => true, 'message' => 'Phone number updated successfully'];

                }



                return ['status' => false, 'message' => __('message.some_thing_went_wrong')];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while updating phone number'];

        }

    }

   public function deleteUserImage($data)
{
    try {

        $user = $this->userRepository->find($data['user_id']);

        if (!$user) {
            return [
                'status' => false,
                'message' => 'User not found'
            ];
        }


        $images = json_decode($user->images, true);

        if (!is_array($images)) {
            $images = [];
        }

        // ❗ prevent deleting last image
        if (count($images) <= 1) {
            return [
                'status' => false,
                'message' => 'At least one image is required so firstly add one image and then delete the existing one'
            ];
        }

        // ✅ normalize request image
        $deleteImage = str_replace('\\', '/', $data['image']);

        $updatedImages = [];

        foreach ($images as $img) {

            $dbImage = str_replace('\\', '/', $img);

            // ✅ STRICT MATCH ONLY (no partial match)
            if ($dbImage !== $deleteImage) {
                $updatedImages[] = $img;
            }
        }

        // if nothing changed → stop (prevents full wipe bug)
        if (count($updatedImages) === 0) {
            return [
                'status' => false,
                'message' => 'Image delete aborted to prevent data loss'
            ];
        }

        // delete from storage
        Storage::disk('public')->delete($deleteImage);

        // save back safely (keep JSON format)
        $user->images = json_encode(array_values($updatedImages));
        $user->save();

        return [
            'status' => true,
            'message' => 'Image deleted successfully'
        ];

    } catch (\Exception $e) {

        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}

}