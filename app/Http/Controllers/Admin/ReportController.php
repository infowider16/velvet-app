<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Services\Admin\ReportService;
use Illuminate\Http\Request;
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

    public function index()
    {
        try {
            /*
             * Show report listing page
             */
            return view('admin.pin-reports');

        } catch (\Exception $e) {
            /*
             * Log controller error
             */
            Log::error('Report index failed: ' . $e->getMessage());

            abort(500);
        }
    }

    public function reportList(Request $request)
    {
        try {
            return $this->reportService->getReportDataTable();
        } catch (\Exception $e) {
            /*
             * Log controller error
             */
            Log::error('Report list retrieval failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve report data'], 500);
        }
    }
}