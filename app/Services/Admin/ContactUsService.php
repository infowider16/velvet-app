<?php



namespace App\Services\Admin;



use App\Contracts\Repositories\ContactUsRepositoryInterface;

use App\Contracts\Services\AdminContactUsServiceInterface; // added import

use App\Services\BaseService;

use Yajra\DataTables\Facades\DataTables;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str; 

use Illuminate\Support\Facades\URL; 



class ContactUsService extends BaseService implements AdminContactUsServiceInterface

{

    protected ContactUsRepositoryInterface $contactUsRepository;



    public function __construct(ContactUsRepositoryInterface $contactUsRepository)

    {

        $this->contactUsRepository = $contactUsRepository;

    }



    public function getContactListDataTable()

    {

        try {

            return $this->handleDataTableCall(function () {

            $contacts = $this->contactUsRepository->getAllData();
          

            return DataTables::of($contacts)

                ->addIndexColumn()

                ->addColumn('user_id', function ($row) {
                    return $row->user_id ? '#'.$row->user_id : '-';
                })

                ->addColumn('name', function ($row) {

                    if (!empty($row->user_id)) {

                        return '<a href="' . route('admin.user.show', $row->user_id) . '" class="text-primary">
                                    ' . e($row->name ?: '-') . '
                                </a>';
                    }

                    return e($row->name ?: '-');
                })

                ->addColumn('login_account', function ($row) {

                    if ($row->user?->email_id) {
                        return $row->user->email_id;
                    }

                    if ($row->user?->phone_number) {
                        return ($row->user->phone_code ?? '') . $row->user->phone_number;
                    }

                    return '-';
                })

                ->addColumn('email', function ($row) {

                    return $row->email ?: '-';
                })

                ->addColumn('subject', function ($row) {

                    return $row->subject ?: '-';
                })

                ->addColumn('message', function ($row) {

                    if (!$row->message) {
                        return '-';
                    }

                    $message = strip_tags($row->message);

                    $words = preg_split('/\s+/', $message);

                    $shortMessage = implode(' ', array_slice($words, 0, 20));

                    $html = '<div class="message-content">' . e($shortMessage);

                    if (count($words) > 20) {

                        $html .= '...</div>

                            <button type="button"
                                class="btn btn-primary btn-sm view-message-btn mt-2"
                                data-message="' . e($message) . '">
                                View More
                            </button>';
                    } else {

                        $html .= '</div>';
                    }

                    return $html;
                })

               ->addColumn('image', function ($row) {

                    if (empty($row->image)) {
                        return '-';
                    }

                    $img = $row->image;

                    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {

                        $src = $img;

                    } elseif (strpos($img, '/storage/') === 0) {

                        $src = $img;

                    } else {

                        $src = asset('storage/' . ltrim($img, '/'));
                    }

                    return '
                        <a href="' . e($src) . '" data-lightbox="support-ticket">
                            <img src="' . e($src) . '"
                                style="
                                    width:70px;
                                    height:70px;
                                    object-fit:cover;
                                    border-radius:6px;
                                    border:1px solid #ddd;
                                    cursor:pointer;
                                ">
                        </a>
                    ';
                })

                ->addColumn('date_time', function ($row) {

                    return $row->created_at
                        ? $row->created_at->format('d M Y h:i A')
                        : '-';
                })


                ->addColumn('created_at', function ($row) {

                    return $row->created_at
                        ? $row->created_at->format('Y-m-d')
                        : '-';
                })

                ->addColumn('status', function ($row) {

                    switch ($row->status) {

                        case 'Pending':
                            $badge = 'warning';
                            break;

                        case 'Open':
                            $badge = 'primary';
                            break;

                        case 'In Progress':
                            $badge = 'info';
                            break;

                        case 'Resolved':
                            $badge = 'success';
                            break;

                        default:
                            $badge = 'secondary';
                    }

                    return '
                        <span class="badge badge-' . $badge . '">
                            '.$row->status.'
                        </span>
                    ';
                })

                ->addColumn('action', function ($row) {
                    
                    if (empty($row->user_id)) {
                        return '';
                    }

                    $statuses = [
                        'Pending',
                        'Open',
                        'In Progress',
                        'Resolved'
                    ];

                    $html = '
                        <div class="d-flex flex-column gap-2">

                            <!-- Status Change -->
                            <select class="form-control change-status mb-2"
                                data-id="'.$row->id.'"
                                style="min-width:150px;">';

                    foreach ($statuses as $status) {

                        $selected = $row->status == $status ? 'selected' : '';

                        $html .= '
                            <option value="'.$status.'" '.$selected.'>
                                '.$status.'
                            </option>';
                    }
                    
                    $html .= '
                        </select>

                        <!-- Buttons -->
                        <div class="gap-2">
                            <!-- View -->
                            <a href="'.route('admin.user.show', $row->user_id).'"
                                class="mt-2 btn btn-info text-nowrap">
                                View
                            </a>
                        </div>
                    ';

                    return $html;
                })

               ->rawColumns([
                    'name',
                    'message',
                    'image',
                    'status',
                    'action'
                ])

                ->make(true);

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }

    public function changeStatus(array $data)
    {
        try {
            
            $this->contactUsRepository->updateStatus(
                ['id' => $data['id']],
                ['status' => $data['status']]
            );
            
            return [
                'success' => true,
                'message' => 'Status updated successfully'
            ];

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            throw $e;
        }
    }



}

