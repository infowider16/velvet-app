<?php

namespace App\Http\Controllers\Admin;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;

class DashboardController extends BaseController
{

 public function index()
    {
        try {
            return view('admin.dashboard');
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->sendError(__('message.some_thing_went_wrong'), [], 500);
        }
    }
}