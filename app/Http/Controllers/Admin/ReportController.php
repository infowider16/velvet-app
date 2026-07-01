<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ChangeReportStatusRequest;
use Illuminate\Support\Facades\Log;

class ReportController extends BaseController
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        /*
         * Assign service dependency
         */
        $this->reportService = $reportService;
    }

   
     public function userReports()
    {
        try {
            /*
             * Show report listing page
             */
            return view('admin.user-reports');

        } catch (\Exception $e) {
            /*
             * Log controller error
             */
            Log::error('Report index failed: ' . $e->getMessage());

            abort(500);
        }
    }

    public function userReportsList(Request $request)
    {
        try {
            return $this->reportService->getUserReportDataTable($request);
        } catch (\Exception $e) {
            /*
             * Log controller error
             */
            Log::error('Report list retrieval failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve report data'], 500);
        }
    }
    public function groupReports()
    {
        try {

            /*
            * Show group report listing page
            */
            return view('admin.group-reports');
        } catch (\Exception $e) {
            /*
            * Log controller error
            */
            Log::error('Group report index failed: ' . $e->getMessage());
            abort(500);
        }
    }

    public function groupReportsList(Request $request)
    {
        try {
            /*
            * Return group reports datatable
            */
            return $this->reportService->getGroupReportDataTable($request);
        } catch (\Exception $e) {
            /*
            * Log controller error
            */
            Log::error('Group report list retrieval failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve group report data'
            ], 500);
        }

    }

    public function pinReports()
    {
        try {
            /*
            * Show pin report listing page
            */
            return view('admin.pin-reports');
        } catch (\Exception $e) {
            /*
            * Log controller error
            */
            Log::error('Pin report index failed: ' . $e->getMessage());
            abort(500);
        }
    }

    public function pinReportsList(Request $request)
    {
        try {
            /*
            * Return pin reports datatable
            */
            return $this->reportService->getPinReportDataTable($request);
        } catch (\Exception $e) {

            /*
            * Log controller error
            */
            Log::error('Pin report list retrieval failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve pin report data'
            ], 500);
        }

    }

    public function changeStatus(ChangeReportStatusRequest $request)
    {
        try {

            /*
            * Change report status
            */
            return $this->reportService->changeStatus($request);

        } catch (\Exception $e) {

            /*
            * Log error
            */
            Log::error('Report status change failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function deleteReport(Request $request)
    {
        try {
            /*
            * Delete report
            */
            return $this->reportService->deleteReport($request);
        } catch (\Exception $e) {
            /*
            * Log error
            */
            Log::error('Report deletion failed: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }


}