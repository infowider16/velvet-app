<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Services\TransactionService;
use Exception;
use Illuminate\Support\Facades\Log;

class TransactionController extends BaseController
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function success(Request $request)
    {
        try {
            $allData=$request->all();
            $transaction=$this->transactionService->storeTransaction($allData);
            return $this->sendResponse($transaction, __('message.transaction_fetched_successfully'));
        } catch (Exception $e) {
            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage()
            );

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
