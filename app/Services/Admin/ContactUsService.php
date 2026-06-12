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

                $contacts = $this->contactUsRepository->all();



                return DataTables::of($contacts)

                    ->addIndexColumn()

                    ->addColumn('name', function ($row) {
                        if (!empty($row->user_id)) {
                            return '<a href="' . route('admin.user.show', $row->user_id) . '" class="text-primary">
                                        ' . e($row->name ?: '-') . '
                                    </a>';
                        }

                        return '<span class="text-muted" data-bs-toggle="tooltip" title="User not found">
                                    ' . e($row->name ?: '-') . '
                                </span>';
                    })

                    ->addColumn('email', function ($row) {

                        return $row->email ?: '-';

                    })

                    ->addColumn('image', function ($row) {

                        // Show image if available in database

                        if (empty($row->image)) {

                            return '-';

                        }

                        $img = $row->image;

                        // If already full URL, use it

                        if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {

                            $src = $img;

                        } elseif (strpos($img, '/storage/') === 0) {

                            $src = $img;

                        } else {

                            // assume storage path like "user_images/filename.jpg"

                            $src = asset('storage/' . ltrim($img, '/'));

                        }

                        return '<img src="' . e($src) . '" alt="contact-image" style="max-width:60px;max-height:60px;object-fit:cover;border-radius:4px;" />';

                    })

                    ->addColumn('subject', function ($row) {

                        if (!$row->subject) return '-';

                        

                        $subject = $row->subject;

                        if (strlen($subject) > 30) {

                            $shortText = Str::limit($subject, 30, '');

                            return '<span class="short-text">' . $shortText . '...</span>

                                    <span class="full-text" style="display:none;">' . $subject . '</span>

                                    <br><button class="btn btn-link btn-sm p-0 toggle-text" data-type="subject">Read More</button>';

                        }

                        return $subject;

                    })

                    ->addColumn('message', function ($row) {

                        if (!$row->message) {
                            return '-';
                        }

                        $message = $row->message;
                        $words = preg_split('/\s+/', strip_tags($message));
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

                    ->addColumn('created_at', function ($row) {

                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';

                    })

                   

                    ->rawColumns(['subject', 'message', 'image' ,'name'])

                    ->make(true);

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }



}

