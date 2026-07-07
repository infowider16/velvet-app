<?php



namespace App\Services\Admin;



use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\AdminUserServiceInterface;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupReport;
use App\Models\Message;
use  App\Models\PinMark;
use  App\Models\PinMarkComment;
use  App\Models\PinMarkLike;
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

    private function getImageUrl(?string $path, string $fallback = 'assets/images/no-image.png'): string
    {
        if (empty($path)) {
            return asset($fallback);
        }

        if (preg_match('#^(?:https?://|//)#i', $path)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
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

                return $this->userRepository->getByWhere(
                    ['id' => $id],
                    [],
                    ['*'],
                    [
                        'groups',
                        'groupMembers',
                        'groupReports',
                        'reportedGroups',
                        'sentMessages',
                        'receivedMessages'
                    ],
                    [],
                    'first'
                );

            }, null); 
         } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->errorResponse('Something went wrong while fetching user details');

        }

    }

    public function getUserGroupMembersDataTable(Request $request, $userId)
    {
        try {
            return $this->handleDataTableCall(function () use ($userId) {
                $query = GroupMember::with(['group', 'user','group.members'])
                    ->where('user_id', $userId)
                    ->orderBy('id', 'desc');

                return DataTables::of($query)
                    ->addColumn('group_info', function ($row) {
                        $group = $row->group;
                        $imageUrl = $group && $group->image ? $this->getImageUrl($group->image, 'assets/images/no-image.png') : asset('assets/images/no-image.png');
                        $groupName = $group?->name ?: '-';
                        $memberCount = $group?->members ? $group->members->count() : 0;
                       
                        return '<div class="group-cell">'
                            . '<img src="' . $imageUrl . '" alt="Group" />'
                            . '<div class="group-meta" style="min-width:0;">'
                            . '<div class="name">' . htmlspecialchars($groupName) . '</div>'
                            . '<div class="meta-label">Members: ' . $memberCount . '</div>'
                            . '</div>'
                            . '</div>';
                    })
                    ->addColumn('member_info', function ($row) {
                        $user = $row->user;
                        $images = [];

                        if ($user && !empty($user->images)) {
                            $images = is_array($user->images)
                                ? $user->images
                                : json_decode($user->images, true);
                        }

                        $imageUrl = !empty($images[0])
                            ? $this->getImageUrl($images[0], 'assets/images/user-default.jpg')
                            : asset('assets/images/user-default.jpg');

                        $userName = $user?->name ?: 'Unknown';
                        return '<div class="group-cell">'
                            . '<img src="' . $imageUrl . '" alt="Member" />'
                            . '<div class="group-meta" style="min-width:0;">'
                            . '<div class="name">' . htmlspecialchars($userName) . '</div>'
                            . '</div>'
                            . '</div>';
                    })
                    ->editColumn('role', function ($row) {
                        return $row->role ?: '-';
                    })
                    ->editColumn('is_member_permission', function ($row) {
                        if ($row->is_member_permission === null) {
                            return '-';
                        }
                        return $row->is_member_permission == 1 ? 'Allowed' : 'Not allowed';
                    })
                    ->editColumn('status', function ($row) {
                        if ($row->status === null) {
                            return '-';
                        }
                        return $row->status == 0 ? 'Unblock' : ($row->status == 1 ? 'Block' : 'Leave');
                    })
                    ->editColumn('unread_count', function ($row) {
                        return $row->unread_count ?? 0;
                    })
                    ->editColumn('accepted_at', function ($row) {
                        return $row->accepted_at ? date('Y-m-d H:i', strtotime($row->accepted_at)) : '-';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';
                    })
                    ->editColumn('group_status', function ($row) {
                        return $row->group_status ?: '-';
                    })
                    ->addColumn('action', function ($row) {

                        return '
                            <button type="button"
                                class="btn btn-sm btn-info group-detail-btn"
                                data-id="'.$row->group_id.'">
                                Group Detail
                            </button>

                            <button type="button"
                                class="btn btn-sm btn-primary group-members-btn"
                                data-id="'.$row->group_id.'">
                                Group Members
                            </button>
                        ';
                    })
                    ->rawColumns(['group_info','member_info', 'action'])
                    ->make(true);
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return response()->json(['status' => false, 'message' => 'Something went wrong while fetching group members'], 500);
        }
    }

    public function getUserGroupReportsDataTable(Request $request, $userId)
    {
        try {
            return $this->handleDataTableCall(function () use ($userId) {
                $query = GroupReport::with(['group', 'reporter', 'reportedUser'])
                    ->where('user_id', $userId)
                    ->orderBy('id', 'desc');

                return DataTables::of($query)
                    ->addColumn('group_info', function ($row) {
                        $group = $row->group;
                        $groupName = $group?->name ?? '-';
                        $imageUrl = $group && $group->image
                            ? $this->getImageUrl($group->image, 'assets/images/no-image.png')
                            : asset('assets/images/no-image.png');

                        return '<div class="group-cell">'
                            . '<img src="' . $imageUrl . '" alt="Group" />'
                            . '<div class="group-meta">'
                            . '<div class="name">' . htmlspecialchars($groupName) . '</div>'
                            . '</div>'
                            . '</div>';
                    })
                    ->addColumn('reported_user_info', function ($row) {
                        $reportedUser = $row->reportedUser;
                        $userName = $reportedUser?->name ?: 'Unknown User';
                        $images = [];

                        if ($reportedUser && !empty($reportedUser->images)) {
                            $images = is_array($reportedUser->images)
                                ? $reportedUser->images
                                : json_decode($reportedUser->images, true);
                        }

                        $imageUrl = !empty($images[0])
                            ? $this->getImageUrl($images[0], 'assets/images/user-default.jpg')
                            : asset('assets/images/user-default.jpg');

                        return '<div class="group-cell">'
                            . '<img src="' . $imageUrl . '" alt="Reported User" />'
                            . '<div class="group-meta">'
                            . '<div class="name">' . htmlspecialchars($userName) . '</div>'
                            . '<div class="meta-label">Reported User</div>'
                            . '</div>'
                            . '</div>';
                    })
                    ->addColumn('reporter_info', function ($row) {
                        $reporter = $row->reporter;
                        $reporterName = $reporter?->name ?: 'Unknown Reporter';
                        $images = [];

                        if ($reporter && !empty($reporter->images)) {
                            $images = is_array($reporter->images)
                                ? $reporter->images
                                : json_decode($reporter->images, true);
                        }

                        $imageUrl = !empty($images[0])
                            ? $this->getImageUrl($images[0], 'assets/images/user-default.jpg')
                            : asset('assets/images/user-default.jpg');

                        return '<div class="group-cell">'
                            . '<img src="' . $imageUrl . '" alt="Reporter" />'
                            . '<div class="group-meta">'
                            . '<div class="name">' . htmlspecialchars($reporterName) . '</div>'
                            . '<div class="meta-label">Reporter</div>'
                            . '</div>'
                            . '</div>';
                    })
                    ->addColumn('report_type', function ($row) {
                        return $row->report_type ? ucfirst(str_replace('_', ' ', $row->report_type)) : '-';
                    })
                    ->addColumn('status', function ($row) {
                        return $row->status ?? 'Pending';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';
                    })
                    ->rawColumns(['group_info', 'reported_user_info', 'reporter_info'])
                    ->make(true);
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return response()->json(['status' => false, 'message' => 'Something went wrong while fetching group reports'], 500);
        }
    }

    public function getUserMessagesDataTable(Request $request, $userId)
    {
        try {
            return $this->handleDataTableCall(function () use ($userId) {
                $query = Message::with(['sender', 'receiver'])
                    ->where(function ($q) use ($userId) {
                        $q->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                    })
                    ->orderBy('id', 'desc');

                return DataTables::of($query)
                    ->addColumn('direction', function ($row) use ($userId) {
                        return $row->sender_id == $userId ? 'Sent' : 'Received';
                    })
                    ->addColumn('other_user', function ($row) use ($userId) {
                        return $row->sender_id == $userId
                            ? ($row->receiver?->name ?? '-')
                            : ($row->sender?->name ?? '-');
                    })
                    ->editColumn('message_text', function ($row) {
                        return $row->message_text ?: ($row->media_type ? ucfirst($row->media_type) : '-');
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';
                    })
                    ->make(true);
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return response()->json(['status' => false, 'message' => 'Something went wrong while fetching messages'], 500);
        }
    }

    public function getUserPinsDataTable(Request $request, $userId)
    {
        try {
            return $this->handleDataTableCall(function () use ($userId) {
                $query = PinMark::with(['user'])
                    ->where('user_id', $userId)
                    ->orderBy('id', 'desc');

                return DataTables::of($query)
                    ->editColumn('pin_message', function ($row) {
                        return $row->pin_message ?: '-';
                    })
                    ->editColumn('country_code', function ($row) {
                        return $row->country_code ?: '-';
                    })
                    ->editColumn('total_like', function ($row) {
                        return $row->total_like ?: 0;
                    })
                    ->addColumn('comment_count', function ($row) {
                        $count = PinMarkComment::where('pin_mark_id', $row->id)->count();
                        return $count;
                    })
                    ->editColumn('status', function ($row) {
                        $statusClass = $row->status == 1 ? 'badge-success' : 'badge-danger';
                        $statusText = $row->status == 1 ? 'Active' : 'Inactive';
                        return "<span class='badge {$statusClass}'>{$statusText}</span>";
                    })
                    ->editColumn('commented_on', function ($row) {
                        return $row->commented_on ? date('Y-m-d H:i', strtotime($row->commented_on)) : '-';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';
                    })
                    ->addColumn('action', function ($row) {
                        $buttons = "<div class='btn-group btn-group-sm' role='group'>";
                        $buttons .= "<button class='btn btn-info pin-comments-btn' data-id='{$row->id}' title='View Comments'>View Comments</button>";
                        $buttons .= "<button class='btn btn-success pin-likes-btn' data-id='{$row->id}' title='View Likes'>View Likes</button>";
                        $buttons .= "<button class='btn btn-warning pin-reports-btn' data-id='{$row->id}' title='View Reports'>View Reports</button>";
                        $buttons .= "</div>";
                        return $buttons;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            });
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return response()->json(['status' => false, 'message' => 'Something went wrong while fetching pins'], 500);
        }
    }

    public function getPinComments($pinId)
    {
        try {
            $comments = PinMarkComment::with(['user', 'pinMark'])
                ->where('pin_mark_id', $pinId)
                ->orderBy('id', 'desc')
                ->get();

            $pinMark = $comments->first()?->pinMark ?: PinMark::find($pinId);

            if (!$pinMark) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pin not found'
                ], 404);
            }

            $commentsHtml = '<div class="pin-comments-list">';
            
            if ($comments->count() > 0) {
                foreach ($comments as $comment) {
                    $userName = $comment->user?->name ?? 'Anonymous';
                    
                    $images = [];

                    if ($comment->user && !empty($comment->user->images)) {
                        $images = is_array($comment->user->images)
                            ? $comment->user->images
                            : json_decode($comment->user->images, true);
                    }

                    $userImage = !empty($images[0])
                        ? $this->getImageUrl($images[0], 'assets/images/user-default.jpg')
                        : asset('assets/images/user-default.jpg');
                    
                    $commentDate = $comment->created_at ? date('Y-m-d H:i', strtotime($comment->created_at)) : '-';
                    $commentText = htmlspecialchars($comment->comment ?: '-', ENT_QUOTES);
                    $likes = $comment->total_like ?: 0;
                    $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&color=667eea&background=f0f0f0&bold=true';
                    
                    $commentsHtml .= "
                        <div class='card mb-3 border-0 shadow-sm' style='border-left: 4px solid #667eea;'>
                            <div class='card-body p-3'>
                                <div class='d-flex align-items-start mb-2'>
                                    <img src='" . asset($userImage) . "' 
                                         alt='{$userName}' 
                                         class='rounded-circle me-3' 
                                         style='width: 40px; height: 40px; object-fit: cover; flex-shrink: 0; border: 2px solid #e9ecef;'
                                         onerror=\"this.src='{$defaultAvatar}'\">
                                    <div class='flex-grow-1 w-100' style='min-width: 0;'>
                                        <div class='d-flex justify-content-between align-items-center mb-1'>
                                            <h6 class='mb-0 font-weight-bold' style='font-size: 0.95rem; color: #2d3748;'>{$userName}</h6>
                                            <span class='badge badge-light' style='font-size: 0.75rem; background: #f0f0f0; color: #e74c3c;'>
                                                <i class='fas fa-heart'></i> {$likes}
                                            </span>
                                        </div>
                                        <small class='text-muted d-block mb-2' style='font-size: 0.8rem;'>
                                            <i class='fas fa-clock me-1'></i>{$commentDate}
                                        </small>
                                        <p class='mb-0' style='font-size: 0.9rem; color: #2d3748; word-break: break-word; line-height: 1.5;'>{$commentText}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ";
                }
            } else {
                $commentsHtml .= '<div class="alert alert-info text-center py-4 mb-0" style="font-size: 0.95rem; background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); border: none; color: #0c5460;">
                    <i class="fas fa-comment-slash me-2" style="font-size: 1.5rem;"></i><br>
                    <strong style="display: block; margin-top: 0.5rem;">No Comments Yet</strong>
                </div>';
            }

            $commentsHtml .= '</div>';

            return response()->json([
                'status' => true,
                'data' => [
                    'pin_message' => $pinMark->pin_message ?? '-',
                    'country_code' => $pinMark->country_code ?? '-',
                    'total_likes' => $pinMark->total_like ?? 0,
                    'comments_html' => $commentsHtml,
                    'comment_count' => $comments->count()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error(__METHOD__ . ' : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching comments'
            ], 500);
        }
    }

    public function getPinLikes($pinId)
    {
        try {
            $likes = PinMarkLike::with(['user', 'pinMark'])
                ->where('pin_mark_id', $pinId)
                ->orderBy('id', 'desc')
                ->get();

            $pinMark = $likes->first()?->pinMark ?: PinMark::find($pinId);

            if (!$pinMark) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pin not found'
                ], 404);
            }

            $likesHtml = '<div class="pin-likes-list">';
            
            if ($likes->count() > 0) {
                foreach ($likes as $like) {
                    $userName = $like->user?->name ?? 'Anonymous';
                    
                    $images = [];

                    if ($like->user && !empty($like->user->images)) {
                        $images = is_array($like->user->images)
                            ? $like->user->images
                            : json_decode($like->user->images, true);
                    }

                    $userImage = !empty($images[0])
                        ? $this->getImageUrl($images[0], 'assets/images/user-default.jpg')
                        : asset('assets/images/user-default.jpg');
                                        
                    $likeDate = $like->created_at ? date('Y-m-d H:i', strtotime($like->created_at)) : '-';
                    $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&color=f5576c&background=f0f0f0&bold=true';
                    
                    $likesHtml .= "
                        <div class='d-flex align-items-center mb-3 pb-3' style='border-bottom: 1px solid #e9ecef;'>
                            <img src='" . asset($userImage) . "' 
                                 alt='{$userName}' 
                                 class='rounded-circle me-3' 
                                 style='width: 44px; height: 44px; object-fit: cover; flex-shrink: 0; border: 2px solid #e9ecef;'
                                 onerror=\"this.src='{$defaultAvatar}'\">
                            <div class='flex-grow-1 w-100' style='min-width: 0;'>
                                <h6 class='mb-1 font-weight-bold' style='font-size: 0.95rem; color: #2d3748;'>{$userName}</h6>
                                <small class='text-muted d-block' style='font-size: 0.8rem;'>
                                    <i class='fas fa-clock me-1'></i>{$likeDate}
                                </small>
                            </div>
                            <span class='badge badge-danger' style='white-space: nowrap; padding: 0.5rem 0.75rem; font-size: 0.75rem;'>
                                <i class='fas fa-heart me-1'></i> Liked
                            </span>
                        </div>
                    ";
                }
            } else {
                $likesHtml .= '<div class="alert alert-warning text-center py-4 mb-0" style="font-size: 0.95rem; background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: none; color: #856404;">
                    <i class="fas fa-heart-broken me-2" style="font-size: 1.5rem;"></i><br>
                    <strong style="display: block; margin-top: 0.5rem;">No Likes Yet</strong>
                </div>';
            }

            $likesHtml .= '</div>';

            // Truncate pin message for display
            $pinMessage = (string)($pinMark->pin_message ?? '-');
            if (strlen($pinMessage) > 80) {
                $pinMessage = substr($pinMessage, 0, 80) . '...';
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'pin_id' => $pinMark->id,
                    'pin_message' => $pinMessage,
                    'pin_country_code' => $pinMark->country_code ?? '-',
                    'pin_total_likes' => $pinMark->total_like ?? 0,
                    'likes_html' => $likesHtml,
                    'likes_count' => $likes->count()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error(__METHOD__ . ' : ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching likes'
            ], 500);
        }
    }

    public function getPinReports($pinId)
    {
        try {
        
            $pinMark = PinMark::with(['pinReports', 'pinReports.reporter'])->find($pinId);
          
            if (!$pinMark) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pin not found'
                ], 404);
            }

            
            $reportsHtml = '<div class="pin-reports-list">';
            
            if (count($pinMark->pinReports) > 0) {
                foreach ($pinMark->pinReports as $report) {

                    $reporterName = $report->reporter?->name ?? 'Anonymous';
                    $reporterEmail = $report->reporter?->gmail_id ?? $report->email ?? '-';

                    $userImage = asset('assets/images/user-default.jpg');

                    if ($report->reporter && !empty($report->reporter->images)) {

                        $images = is_array($report->reporter->images)
                            ? $report->reporter->images
                            : json_decode($report->reporter->images, true);

                        if (!empty($images[0])) {
                            $userImage = $this->getImageUrl($images[0], 'assets/images/user-default.jpg');
                        }
                    }

                    $reportDate = $report->created_at
                        ? $report->created_at->format('Y-m-d H:i')
                        : '-';

                    $status = $report->status ?? 'Pending';

                    $statusClass = match ($status) {
                        'Resolved' => 'badge-success',
                        'Rejected' => 'badge-danger',
                        default => 'badge-warning'
                    };

                    $statusIcon = match ($status) {
                        'Resolved' => 'fa-check-circle',
                        'Rejected' => 'fa-times-circle',
                        default => 'fa-clock'
                    };

                    $reportType = htmlspecialchars($report->report_type ?? '-', ENT_QUOTES);
                    $reason = htmlspecialchars($report->reason ?? '-', ENT_QUOTES);

                    $defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($reporterName) . '&color=667eea&background=f0f0f0&bold=true';

                    $reportsHtml .= "
                        <div class='card mb-3 border-0 shadow-sm' style='border-left: 4px solid #ff6b6b;'>
                            <div class='card-body p-3'>
                                <!-- Reporter Info -->
                                <div class='d-flex align-items-center mb-3'>
                                    <img src='{$userImage}'
                                        alt='{$reporterName}'
                                        class='rounded-circle me-3'
                                        style='width: 44px; height: 44px; object-fit: cover; border: 2px solid #e9ecef;'
                                        onerror=\"this.src='{$defaultAvatar}'\">
                                    
                                    <div class='flex-grow-1'>
                                        <h6 class='mb-1 font-weight-bold' style='font-size: 0.95rem; color: #2d3748;'>{$reporterName}</h6>
                                        <small class='text-muted' style='font-size: 0.8rem;'>
                                            <i class='fas fa-calendar-alt me-1'></i>{$reportDate}
                                        </small>
                                    </div>
                                    
                                    <span class='badge {$statusClass}' style='font-size: 0.75rem; padding: 0.5rem 0.75rem;'>
                                        <i class='fas {$statusIcon} me-1'></i>{$status}
                                    </span>
                                </div>

                                <!-- Report Details -->
                                <div class='bg-light p-3 rounded' style='border: 1px solid #e9ecef;'>
                                    <div class='row mb-2'>
                                        <div class='col-sm-6'>
                                            <small class='text-muted d-block mb-1' style='font-size: 0.75rem; font-weight: 600; text-transform: uppercase;'>
                                                <i class='fas fa-flag me-1' style='color: #ff6b6b;'></i>Report Type
                                            </small>
                                            <p class='mb-0' style='font-size: 0.9rem; color: #2d3748; font-weight: 500;'>{$reportType}</p>
                                        </div>
                                        <div class='col-sm-6'>
                                            <small class='text-muted d-block mb-1' style='font-size: 0.75rem; font-weight: 600; text-transform: uppercase;'>
                                                <i class='fas fa-envelope me-1' style='color: #4c6ef5;'></i>Reporter Email
                                            </small>
                                            <p class='mb-0' style='font-size: 0.85rem; color: #2d3748; word-break: break-all;'>{$reporterEmail}</p>
                                        </div>
                                    </div>

                                    <div class='mt-3 pt-2 border-top'>
                                        <small class='text-muted d-block mb-2' style='font-size: 0.75rem; font-weight: 600; text-transform: uppercase;'>
                                            <i class='fas fa-exclamation-triangle me-1' style='color: #ffa500;'></i>Reason
                                        </small>
                                        <p class='mb-0' style='font-size: 0.9rem; color: #2d3748; line-height: 1.5;'>{$reason}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ";
                }
            } else {
                $reportsHtml .= '<div class="alert alert-success text-center py-4 mb-0" style="font-size: 0.95rem; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: none; color: #155724;">
                    <i class="fas fa-shield-alt me-2" style="font-size: 1.5rem;"></i><br>
                    <strong style="display: block; margin-top: 0.5rem;">No Reports</strong>
                    <small>This pin has no reports yet</small>
                </div>';
            }

            $reportsHtml .= '</div>';

            return response()->json([
                'status' => true,
                'data' => [
                    'pin_id' => $pinMark->id,
                    'pin_message' => strlen($pinMark->pin_message ?? '') > 80 ? substr($pinMark->pin_message, 0, 80) . '...' : ($pinMark->pin_message ?? '-'),
                    'pin_country_code' => $pinMark->country_code ?? '-',
                    'pin_total_likes' => $pinMark->total_like ?? 0,
                    'reports_html' => $reportsHtml,
                    'reports_count' => count($pinMark->pinReports)
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error(__METHOD__ . ' : ' . $e->getMessage());
            return response()->json([
                'status' => true,
                'data' => [
                    'reports_html' => '<div class="alert alert-info text-center mb-0" style="font-size: 0.9rem;"><i class="fas fa-info-circle mr-2"></i>Reports feature not available</div>',
                    'reports_count' => 0
                ]
            ]);
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