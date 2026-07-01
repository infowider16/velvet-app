<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Contracts\Services\AdminContactUsServiceInterface;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Http\Requests\Admin\UpdateContactStatusRequest;


class ContactUsController extends BaseController 

{

    protected AdminContactUsServiceInterface $contactUsService;



    public function __construct(AdminContactUsServiceInterface $contactUsService)

    {

        $this->contactUsService = $contactUsService;

    }



    public function index()

    {

        try {

            return view('admin.contact-list');

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong');

        }

    }



    public function getContactList()

    {

        try {

            return $this->contactUsService->getContactListDataTable();

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }

    public function changeStatus(UpdateContactStatusRequest $request)
    {
        try {

            $response = $this->contactUsService->changeStatus($request->validated());

            return response()->json($response);

        } catch (\Exception $e) {

            Log::error(
                "Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage()
            );

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }


}

 

