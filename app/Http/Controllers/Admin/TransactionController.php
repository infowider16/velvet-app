<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Services\TransactionService;

class TransactionController extends BaseController

{

    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index()

    {
        return view('admin.transaction-list');
    }


    public function transactionList(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionService->getTransactionListDataTable($request);
        }
        return view('admin.transaction-list');
    }

    // Ghost Transactions
    public function ghostTransactions()
    {
        return view('admin.ghost-transaction');
    }

    public function ghostTransactionList(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionService->getTransactionListByType($request, 2); // 2 = Ghost
        }
        return view('admin.ghost-transaction');
    }

    // Boost Transactions
    public function boostTransactions()
    {
        return view('admin.boost-transaction');
    }

    public function boostTransactionList(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionService->getTransactionListByType($request, 1); // 1 = Boost
        }
        return view('admin.boost-transaction');
    }

    // Pin Transactions
    public function pinTransactions()
    {
        return view('admin.pin-transaction');
    }

    public function pinTransactionList(Request $request)
    {
        if ($request->ajax()) {
            return $this->transactionService->getTransactionListByType($request, 0); // 0 = Pin
        }
        return view('admin.pin-transaction');
    }


}