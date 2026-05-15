<?php

namespace App\Services\Admin;

use App\Repositories\Eloquent\GroupRepository;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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

    public function getReportDataTable()
    {
        try {
            /*
            * Get report query from repository
            */
            $reports = $this->groupRepository->getPinReport();

            return DataTables::of($reports)
                ->addIndexColumn()

                ->addColumn('pin', function ($row) {
                    /*
                    * Return pin message
                    */
                    return $row->pinmark->pin_message ?? 'N/A';
                })

                ->addColumn('reporter_name', function ($row) {
                    /*
                    * Return reporter name
                    */
                    return $row->reporter->name ?? 'N/A';
                })

                ->addColumn('reporter_email', function ($row) {
                    /*
                    * Return reporter email
                    */
                    return $row->reporter->gmail_id ?? $row->email ?? 'N/A';
                })

                ->addColumn('group_name', function ($row) {
                    /*
                    * Return group name
                    */
                    return $row->group->name ?? 'N/A';
                })

                ->addColumn('created_at', function ($row) {
                    /*
                    * Return formatted date
                    */
                    return $row->created_at
                        ? $row->created_at->format('d M Y h:i A')
                        : 'N/A';
                })

                ->addColumn('action', function ($row) {

                    /*
                    * Generate view button
                    */
                    return '
                        <button 
                            class="btn btn-sm btn-info view-report-btn"
                            data-pin="' . e($row->pinmark->pin_message ?? 'N/A') . '"
                            data-reporter="' . e($row->reporter->name ?? 'N/A') . '"
                            data-email="' . e($row->reporter->gmail_id ?? $row->email ?? 'N/A') . '"
                            data-group="' . e($row->group->name ?? 'N/A') . '"
                            data-report-type="' . e($row->report_type) . '"
                            data-reason="' . e($row->reason) . '"
                            data-image="' . asset('storage/' . $row->image) . '"
                            data-created="' . ($row->created_at
                                ? $row->created_at->format('d M Y h:i A')
                                : 'N/A') . '"
                        >
                            <i class="fas fa-eye"></i> View
                        </button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);

        } catch (\Exception $e) {

            /*
            * Log service error
            */
            Log::error('Report DataTable service failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while fetching reports',
            ], 500);
        }

    }
}