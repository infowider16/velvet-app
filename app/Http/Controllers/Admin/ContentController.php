<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Contracts\Services\AdminContentServiceInterface;

use App\Http\Requests\Admin\UpdateContentRequest;

use Illuminate\Support\Facades\Log;



class ContentController extends BaseController

{

    protected AdminContentServiceInterface $contentService;



    public function __construct(AdminContentServiceInterface $contentService)

    {

        $this->contentService = $contentService;

    }



    public function update(UpdateContentRequest $request, $id)

    {

        try {

            $result = $this->contentService->updateContent($request, $id);

            

            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);

            }

            

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);

        }

    }



    public function index()

    {

        try {

            $contents = $this->contentService->getAllContents();
           
            return view('admin.content.index', compact('contents'));

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong');

        }

    }

}