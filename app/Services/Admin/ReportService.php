<?php

namespace App\Services\Admin;

use App\Repositories\Eloquent\GroupRepository;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class ReportService
{
    protected GroupRepository $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        /*
         * Assign repository dependency
         */
        $this->groupRepository = $groupRepository;
    }

    public function getUserReportDataTable($request)
    {
        try {


            /*
            * Get user reports query from repository
            */
            $reports = $this->groupRepository->getUserReports($request);

            return DataTables::of($reports)

                ->addIndexColumn()

                ->addColumn('reported_id', function ($row) {

                    /*
                    * Return report id
                    */
                    return $row->id ? "#" . $row->id : '';
                })

                ->addColumn('reported_user_name', function ($row) {

                    /*
                    * Return reported user name
                    */
                    return $row->reportedUser->name ?? 'N/A';
                })

                ->addColumn('reported_user_id', function ($row) {

                    /*
                    * Return reported user id
                    */
                    return $row->reportedUser->id
                        ? "#" . $row->reportedUser->id
                        : 'N/A';
                })

                ->addColumn('reported_user_login', function ($row) {

                    /*
                    * Return reported user login
                    */
                    if ($row->reportedUser?->email_id) {
                        return $row->reportedUser->email_id;
                    }

                    if ($row->reportedUser?->phone_number) {
                        return ($row->reportedUser->phone_code ?? '')
                            . $row->reportedUser->phone_number;
                    }

                    return '-';
                })

                ->addColumn('reporter_name', function ($row) {

                    /*
                    * Return reporter name
                    */
                    return $row->reporter->name ?? 'N/A';
                })

                ->addColumn('reporter_id', function ($row) {

                    /*
                    * Return reporter id
                    */
                    return $row->reporter->id
                        ? "#" . $row->reporter->id
                        : 'N/A';
                })

                ->addColumn('reporter_login', function ($row) {

                    /*
                    * Return reporter login
                    */
                    if ($row->reporter?->email_id) {
                        return $row->reporter->email_id;
                    }

                    if ($row->reporter?->phone_number) {
                        return ($row->reporter->phone_code ?? '')
                            . $row->reporter->phone_number;
                    }

                    return '-';
                })

                ->addColumn('report_type', function ($row) {

                    /*
                    * Return report type
                    */
                    return $row->report_type ?? 'N/A';
                })

                ->addColumn('reason', function ($row) {

                    /*
                    * Return report reason
                    */
                    return $row->reason ?? 'N/A';
                })

                ->addColumn('screenshot', function ($row) {

                    /*
                    * Return screenshot preview
                    */
                    if (!$row->image) {
                        return 'N/A';
                    }

                    $imageUrl = asset('storage/' . $row->image);

                    return '
                        <a href="' . $imageUrl . '" 
                        data-lightbox="report-image-' . $row->id . '">

                            <img 
                                src="' . $imageUrl . '" 
                                width="60"
                                height="60"
                                style="
                                    object-fit:cover;
                                    border-radius:8px;
                                    border:1px solid #ddd;
                                "
                            >

                        </a>
                    ';
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
                            ' . ($row->status ?? 'Pending') . '
                        </span>
                    ';
                })

                ->addColumn('action', function ($row) {

                    $statuses = [
                        'Pending',
                        'Open',
                        'In Progress',
                        'Resolved'
                    ];

                    $html = '
                        <div class="d-flex flex-column">

                            <select 
                                class="form-control change-status mb-2"
                                data-id="' . $row->id . '"
                                style="min-width:150px;">';

                    foreach ($statuses as $status) {

                        $selected = ($row->status == $status)
                            ? 'selected'
                            : '';

                        $html .= '
                            <option value="' . $status . '" ' . $selected . '>
                                ' . $status . '
                            </option>
                        ';
                    }

                    $html .= '
                            </select>

                            <a href="' . route('admin.user.show', $row->user_id) . '"
                            class="btn btn-info btn-sm text-nowrap">
                                View
                            </a>

                        </div>
                    ';

                    return $html;
                })

                ->addColumn('created_at', function ($row) {

                    /*
                    * Return report creation date
                    */
                    return $row->created_at
                        ? $row->created_at->format('Y-m-d H:i:s')
                        : 'N/A';
                })

                ->rawColumns([
                    'screenshot',
                    'status',
                    'action'
                ])

                ->make(true);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error(
                'User report datatable service failed: '
                . $e->getMessage()
            );

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching user reports',
            ], 500);
        }

    }
    
    public function getGroupReportDataTable($request)
    {
        try {


            /*
            * Get group reports query from repository
            */
            $reports = $this->groupRepository->getGroupReports($request);

            return DataTables::of($reports)

                ->addIndexColumn()
                ->addColumn('reported_id', function ($row) {

                    /*
                    * Return report id
                    */
                    return $row->id ? "#" . $row->id : '';
                })

                ->addColumn('group_name', function ($row) {

                    /*
                    * Return group name
                    */
                    return $row->group->name ?? 'N/A';
                })

                ->addColumn('group_id', function ($row) {

                    /*
                    * Return group id
                    */
                    return "#" . $row->group->id ?? 'N/A';
                })

                ->addColumn('group_owner', function ($row) {

                    /*
                    * Return group owner
                    */
                    return $row->group->creator->name ?? 'N/A';
                })

                ->addColumn('members_count', function ($row) {

                    /*
                    * Return group members count
                    */
                    return $row->group->members->count() ?? 0;
                })

                ->addColumn('reporter_name', function ($row) {

                    /*
                    * Return reporter name
                    */
                    return $row->reporter->name ?? 'N/A';
                })

                ->addColumn('reporter_id', function ($row) {

                    /*
                    * Return reporter id
                    */
                    return "#" . $row->reporter->id ?? 'N/A';
                })

                ->addColumn('reporter_login', function ($row) {

                    /*
                    * Return reporter login
                    */
                    if ($row->reporter?->email_id) {
                        return $row->reporter->email_id;
                    }

                    if ($row->reporter?->phone_number) {
                        return ($row->reporter->phone_code ?? '')
                            . $row->reporter->phone_number;
                    }

                    return '-';
                })

                  ->addColumn('screenshot', function ($row) {

                    /*
                    * Return screenshot preview
                    */
                    if (!$row->image) {
                        return 'N/A';
                    }

                    $imageUrl = asset('storage/' . $row->image);

                    return '
                        <a href="' . $imageUrl . '" 
                        data-lightbox="report-image-' . $row->id . '">

                            <img 
                                src="' . $imageUrl . '" 
                                width="60"
                                height="60"
                                style="
                                    object-fit:cover;
                                    border-radius:8px;
                                    border:1px solid #ddd;
                                "
                            >

                        </a>
                    ';
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
                            ' . ($row->status ?? 'Pending') . '
                        </span>
                    ';
                })

               ->addColumn('action', function ($row) {

                    $statuses = [
                        'Pending',
                        'Open',
                        'In Progress',
                        'Resolved'
                    ];

                    $html = '
                        <div class="d-flex flex-column">

                            <select 
                                class="form-control change-status mb-2"
                                data-id="' . $row->id . '"
                                style="min-width:150px;">';

                    foreach ($statuses as $status) {

                        $selected = ($row->status == $status)
                            ? 'selected'
                            : '';

                        $html .= '
                            <option value="' . $status . '" ' . $selected . '>
                                ' . $status . '
                            </option>
                        ';
                    }

                    $html .= '
                            </select>

                            <button 
                                class="btn btn-info btn-sm mb-2 view-group-report-btn"

                                data-report-id="#' . $row->id . '"
                                data-group-name="' . e($row->group->name ?? 'N/A') . '"
                                data-group-id="#' . ($row->group->id ?? 'N/A') . '"
                                data-group-owner="' . e($row->group->creator->name ?? 'N/A') . '"
                                data-members-count="' . ($row->group->members->count() ?? 0) . '"

                                data-reporter-name="' . e($row->reporter->name ?? 'N/A') . '"
                                data-reporter-id="#' . ($row->reporter->id ?? 'N/A') . '"
                                data-reporter-login="' . e(
                                    $row->reporter->email
                                    ?? (
                                        ($row->reporter->phone_code ?? '') .
                                        ($row->reporter->phone_number ?? '')
                                    )
                                    ?? 'N/A'
                                ) . '"

                                data-report-type="' . e($row->report_type ?? 'N/A') . '"
                                data-reason="' . e($row->reason ?? 'N/A') . '"

                                data-created="' . (
                                    $row->created_at
                                    ? $row->created_at->format('Y-m-d H:i:s')
                                    : 'N/A'
                                ) . '"

                                data-image="' . (
                                    $row->image
                                    ? asset('storage/' . $row->image)
                                    : ''
                                ) . '">

                                View
                            </button>

                            <button 
                                class="btn btn-danger btn-sm delete-report-btn"
                                data-id="' . $row->id . '">

                                Delete
                            </button>

                        </div>
                    ';

                    return $html;
                })
                ->addColumn('created_at', function ($row) {

                    /*
                    * Return report creation date
                    */
                    return $row->created_at
                        ? $row->created_at->format('Y-m-d H:i:s')
                        : 'N/A';
                })

                ->rawColumns([
                    'screenshot',
                    'status',
                    'action'
                ])
                ->make(true);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error('Group report datatable service failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching group reports',
            ], 500);
        }
    }

    public function getPinReportDataTable($request = null)
    {
        try {


            /*
            * Get pin reports query from repository
            */
            $reports = $this->groupRepository->getPinReports($request);

            return DataTables::of($reports)

                ->addIndexColumn()
                ->addColumn('reported_id', function ($row) {

                    /*
                    * Return report id
                    */
                    return $row->id ? "#" . $row->id : '';
                })

                ->addColumn('pin_id', function ($row) {

                    /*
                    * Return pin id
                    */
                    return "#" . $row->pinmark->id ?? 'N/A';
                })

                ->addColumn('pin_preview', function ($row) {

                    /*
                    * Return short pin preview
                    */
                    $message = $row->pinmark?->pin_message;

                    if (!$message) {
                        return 'N/A';
                    }

                    return e(Str::words($message, 10, '...'));
                })
                ->addColumn('pin_author', function ($row) {

                    /*
                    * Return pin author
                    */
                    return $row->pinmark->user->name ?? 'N/A';
                })

                ->addColumn('pin_author_id', function ($row) {

                    /*
                    * Return pin author id
                    */
                    return "#" . $row->pinmark->user->id ?? 'N/A';
                })

                ->addColumn('reporter_name', function ($row) {

                    /*
                    * Return reporter name
                    */
                    return $row->reporter->name ?? 'N/A';
                })

                ->addColumn('reporter_id', function ($row) {

                    /*
                    * Return reporter id
                    */
                    return "#" . $row->reporter->id ?? 'N/A';
                })

                ->addColumn('reporter_login', function ($row) {

                    /*
                    * Return reporter login
                    */
                    if ($row->reporter?->email_id) {
                        return $row->reporter->email_id;
                    }

                    if ($row->reporter?->phone_number) {
                        return ($row->reporter->phone_code ?? '')
                            . $row->reporter->phone_number;
                    }

                    return '-';
                })

                ->addColumn('screenshot', function ($row) {

                    /*
                    * Return screenshot preview
                    */
                    if (!$row->image) {
                        return 'N/A';
                    }

                    return '
                        <a href="' . asset('storage/' . $row->image) . '" target="_blank">
                            <img src="' . asset('storage/' . $row->image) . '" width="60">
                        </a>
                    ';
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
                            ' . ($row->status ?? 'Pending') . '
                        </span>
                    ';
                })
                ->addColumn('created_at', function ($row) {

                    /*
                    * Return report creation date
                    */
                    return $row->created_at
                        ? $row->created_at->format('Y-m-d H:i:s')
                        : 'N/A';
                })

                ->addColumn('action', function ($row) {

                    $statuses = [
                        'Pending',
                        'Open',
                        'In Progress',
                        'Resolved'
                    ];

                    $html = '
                        <div class="d-flex flex-column">

                            <select 
                                class="form-control change-status mb-2"
                                data-id="' . $row->id . '"
                                style="min-width:150px;">';

                    foreach ($statuses as $status) {

                        $selected = ($row->status == $status)
                            ? 'selected'
                            : '';

                        $html .= '
                            <option value="' . $status . '" ' . $selected . '>
                                ' . $status . '
                            </option>
                        ';
                    }

                    $html .= '
                            </select>

                            <button 
                                class="btn btn-info btn-sm view-report-btn"

                                data-pin-id="' . ($row->pinmark->id ?? 'N/A') . '"
                                data-pin-preview="' . e($row->pinmark->pin_message ?? 'N/A') . '"
                                data-pin-author="' . e($row->pinmark->user->name ?? 'N/A') . '"
                                data-pin-author-id="#' . ($row->pinmark->user->id ?? 'N/A') . '"

                                data-reporter-name="' . e($row->reporter->name ?? 'N/A') . '"
                                data-reporter-id="#' . ($row->reporter->id ?? 'N/A') . '"
                                data-reporter-login="' . e(
                                    $row->reporter->gmail_id
                                    ?? $row->reporter->phone
                                    ?? 'N/A'
                                ) . '"

                                data-report-type="' . e($row->report_type ?? 'N/A') . '"
                                data-reason="' . e($row->reason ?? 'N/A') . '"
                                data-created="' . ($row->created_at
                                    ? $row->created_at->format('Y-m-d H:i:s')
                                    : 'N/A') . '"

                                data-image="' . asset('storage/' . $row->image) . '">

                                View
                            </button>
                            
                            <button 
                                class="btn btn-danger mt-1 btn-sm delete-report-btn"
                                data-id="' . $row->id . '">

                                Delete
                            </button>

                        </div>

                    ';

                    return $html;
                })
                ->rawColumns(['screenshot', 'status', 'action'])
                ->make(true);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error('Pin report datatable service failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching pin reports',
            ], 500);
        }
    }

    public function changeStatus($request)
    {
        try {

            /*
            * Update report status
            */
            $updated = $this->groupRepository->updateData(
                ['id' => $request->id],
                ['status' => $request->status]
            );

            /*
            * Check update result
            */
            if (!$updated) {

                return response()->json([
                    'status' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            /*
            * Success response
            */
            return response()->json([
                'status' => true,
                'message' => 'Report status updated successfully'
            ]);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error('Report status service failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to update report status'
            ], 500);
        }
    }

    public function deleteReport($request)
    {
        try {

            /*
            * Delete report
            */
            $deleted = $this->groupRepository->deleteReport(['id' => $request->id]);

            /*
            * Check delete result
            */
            if (!$deleted) {

                return response()->json([
                    'status' => false,
                    'message' => 'Report not found'
                ], 404);
            }

            /*
            * Success response
            */
            return response()->json([
                'status' => true,
                'message' => 'Report deleted successfully'
            ]);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error('Report deletion service failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete report'
            ], 500);
        }
    }


}