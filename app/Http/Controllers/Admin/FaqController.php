<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Contracts\Services\AdminFaqServiceInterface;

use App\Http\Requests\Admin\CreateFaqRequest;

use App\Http\Requests\Admin\UpdateFaqRequest;

use Illuminate\Support\Facades\Log;



class FaqController extends BaseController

{

    protected AdminFaqServiceInterface $faqService;



    public function __construct(AdminFaqServiceInterface $faqService)

    {

        $this->faqService = $faqService;

    }



    public function index()

    {

        try {

            return view('admin.faq.index');

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong');

        }

    }



    public function getFaqList()

    {

        try {

            return $this->faqService->getFaqListDataTable();

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }



    public function create()

    {

        try {

            return view('admin.faq.create');

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong');

        }

    }



    public function store(CreateFaqRequest $request)

    {

        try {

            $result = $this->faqService->createFaq($request);

            

            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }

            

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }



    public function edit($id)

    {

        try {

            $faq = $this->faqService->getFaqDetail($id);

            if (!$faq) {

                return redirect()->route('admin.faq.index')->with('error', 'FAQ not found');

            }

            return view('admin.faq.edit', compact('faq'));

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong');

        }

    }



    public function update(UpdateFaqRequest $request, $id)

    {

        try {

            $result = $this->faqService->updateFaq($request, $id);

            

            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }

            

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }



    public function destroy($id)

    {

        try {

            $result = $this->faqService->deleteFaq($id);

            

            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }

            

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }

}

