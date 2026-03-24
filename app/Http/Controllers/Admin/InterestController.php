<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\BaseController;

use App\Contracts\Services\InterestServiceInterface;
use App\Contracts\Repositories\InterestRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


class InterestController extends BaseController

{

    protected InterestServiceInterface $interestService;

    protected InterestRepositoryInterface $interestRepository;



    public function __construct(InterestServiceInterface $interestService, InterestRepositoryInterface $interestRepository)

    {

        $this->interestService = $interestService;

        $this->interestRepository = $interestRepository;
    }



    public function index()

    {

        try {
            return view('admin.interest');
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }



    public function getInterestList()

    {

        try {

            $interests = $this->interestService->getInterestList();

            // Ensure we have a valid collection
            if (!$interests || $interests->isEmpty()) {
                return datatables()->of(collect())->make(true);
            }

            return datatables()->of($interests)

                ->addColumn('action', function ($row) {
                    $nameTranslations = $row->name_translation ?? [];
                    return '
                        <button class="btn btn-sm btn-info edit-interest"
                            data-id="' . $row->id . '"
                            data-name-en="' . e($nameTranslations['en'] ?? $row->name) . '"
                            data-name-ge="' . e($nameTranslations['ge'] ?? '') . '"
                            data-parent-id="' . e($row->parent_id ?? 0) . '">
                            Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-interest"
                            data-id="' . $row->id . '">
                            Delete
                        </button>
                    ';
                })

                ->addIndexColumn()

                ->rawColumns(['action'])

                ->make(true);
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return datatables()->of(collect())->make(true);
        }
    }



    public function store(Request $request)
    {
        $request->validate([
            'name_translation.en' => 'required|string|max:255',
            'name_translation.ge' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
        ]);

        try {

            $result = $this->interestService->createInterest($request);

            if ($result['status']) {
                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }



    public function edit($id)
    {
        try {
            $interest = $this->interestService->getInterestDetail($id);

            if (!$interest) {
                return response()->json([
                    'status' => false,
                    'message' => 'Interest not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $interest
            ]);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'name_translation.en' => 'required|string|max:255',
            'name_translation.ge' => 'required|string|max:255',
            'parent_id' => 'nullable|integer',
        ]);

        try {
            $result = $this->interestService->updateInterest($request, $id);

            if ($result['status']) {
                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {
            \Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }


    public function destroy($id)

    {

        try {

            $result = $this->interestService->deleteInterest($id);



            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }



            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }

    public function subIndex()

    {

        try {
            return view('admin.subinterest');
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }




    public function subStore(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:interests,id',
            'name_translation.en' => 'required|string|max:255',
            'name_translation.ge' => 'required|string|max:255',
        ]);

        try {
            $payload = [
                'parent_id' => $request->input('parent_id'),
                'name' => $request->input('name_translation.en'),
                'name_translation' => [
                    'en' => $request->input('name_translation.en'),
                    'ge' => $request->input('name_translation.ge'),
                ],
            ];

            $result = $this->interestService->createSubInterest($payload);

            if ($result['status']) {
                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }



    public function subEdit($id)

    {

        try {

            $subInterest = $this->interestService->getSubInterestDetail($id);

            if (!$subInterest) {

                return response()->json(['status' => false, 'message' => 'Sub Interest not found'], 404);
            }

            return response()->json(['status' => true, 'data' => $subInterest]);
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json(['status' => false, 'message' => 'Something went wrong'], 500);
        }
    }



    public function subUpdate(Request $request, $id)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:interests,id',
            'name_translation.en' => 'required|string|max:255',
            'name_translation.ge' => 'required|string|max:255',
        ]);

        try {
            $payload = [
                'parent_id' => $request->input('parent_id'),
                'name' => $request->input('name_translation.en'),
                'name_translation' => [
                    'en' => $request->input('name_translation.en'),
                    'ge' => $request->input('name_translation.ge'),
                ],
            ];

            $result = $this->interestService->updateSubInterest($payload, $id);

            if ($result['status']) {
                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }

            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }


    public function subDestroy($id)

    {

        try {

            $result = $this->interestService->deleteSubInterest($id);



            if ($result['status']) {

                return $this->adminSuccessResponse([], $result['message'], 1, 200);
            }



            return $this->adminErrorResponse($result['message'], [], [], 0, 400);
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->adminErrorResponse('Something went wrong', [], [], 0, 500);
        }
    }



    public function getParentInterests()

    {

        try {

            $parents = $this->interestService->getParentInterests();

            // Convert to array for JSON response
            $parentsArray = $parents ? $parents->toArray() : [];

            if (empty($parentsArray)) {
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'message' => 'No parent interests found'
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $parentsArray,
                'message' => 'Parent interests retrieved successfully'
            ]);
        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'data' => []
            ], 500);
        }
    }

    public function getSubInterestList()
    {
        try {
            $subInterests = $this->interestService->getSubInterestList();

            if (!$subInterests || $subInterests->isEmpty()) {
                return datatables()->of(collect())->make(true);
            }

            return datatables()->of($subInterests)
                ->addColumn('parent_name', function ($row) {
                    return $row->parent ? $row->parent->name : '-';
                })
                ->addColumn('action', function ($row) {
                    $nameTranslations = $row->name_translation ?? [];

                    return '
                    <button class="btn btn-sm btn-primary edit-sub-interest"
                        data-id="' . $row->id . '"
                        data-name-en="' . e($nameTranslations['en'] ?? $row->name) . '"
                        data-name-ge="' . e($nameTranslations['ge'] ?? '') . '"
                        data-parent-id="' . e($row->parent_id) . '">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger delete-sub-interest" data-id="' . $row->id . '">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                ';
                })
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return datatables()->of(collect())->make(true);
        }
    }
}
