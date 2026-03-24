<?php



namespace App\Services\Admin;



use App\Contracts\Services\AdminFaqServiceInterface;

use App\Services\BaseService;

use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;

use App\Contracts\Repositories\FaqRepositoryInterface;

use Illuminate\Support\Str;



class FaqService extends BaseService implements AdminFaqServiceInterface

{

    protected FaqRepositoryInterface $faqRepository;



    public function __construct(FaqRepositoryInterface $faqRepository)

    {

        $this->faqRepository = $faqRepository;

    }



    public function getFaqListDataTable()

    {

        try {

            return $this->handleDataTableCall(function () {

                $faqs = $this->faqRepository->all();



                return DataTables::of($faqs)

                    ->addIndexColumn()

                    ->addColumn('question', function ($row) {

                        return $row->question ? Str::limit($row->question, 20) : '-';

                    })

                    ->addColumn('answer', function ($row) {

                        if (!$row->answer) return '-';



                        $answer = $row->answer;

                        if (strlen($answer) > 20) {

                            $shortText = Str::limit($answer, 20, '');

                            return '<span class="short-text">' . $shortText . '...</span>

                                <span class="full-text" style="display:none;">' . $answer . '</span>

                                <br><button class="btn btn-link btn-sm p-0 toggle-text">Read More</button>';

                        }

                        return $answer;

                    })

                    ->addColumn('created_at', function ($row) {

                        return $row->created_at ? date('Y-m-d H:i', strtotime($row->created_at)) : '-';

                    })

                    ->addColumn('action', function ($row) {

                        $editUrl = route('admin.faq.edit', $row->id);



                        $editBtn = '<a href="' . $editUrl . '" class="btn btn-sm btn-primary mr-1">Edit</a>';

                        $deleteBtn = '<button class="btn btn-sm btn-danger ml-1 delete-faq" 

                        data-id="' . $row->id . '">Delete</button>';



                        return $editBtn . $deleteBtn;

                    })

                    ->rawColumns(['answer', 'action'])

                    ->make(true);

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->adminErrorResponse('Something went wrong while fetching FAQs', [], [], 0, 500);

        }

    }



    public function createFaq(Request $request)

    {

        try {

            return $this->handleServiceCall(function () use ($request) {

                $data = [

                    'question' => $request->question,

                    'answer' => $request->answer,

                    'status' => $request->has('status') ? 1 : 0,

                    'sort_order' => $request->sort_order ?? 0

                ];



                $faq = $this->faqRepository->create($data);



                return $faq

                    ? ['status' => true, 'message' => 'FAQ created successfully']

                    : ['status' => false, 'message' => 'Failed to create FAQ'];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while creating FAQ'];

        }

    }



    public function updateFaq(Request $request, $id)

    {

        try {

            return $this->handleServiceCall(function () use ($request, $id) {

                $faq = $this->faqRepository->find($id);

                if (!$faq) {

                    return ['status' => false, 'message' => 'FAQ not found'];

                }



                $data = [

                    'question' => $request->question,

                    'answer' => $request->answer,

                    'status' => $request->has('status') ? 1 : 0,

                    'sort_order' => $request->sort_order ?? 0

                ];



                $updated = $this->faqRepository->update(['id' => $id], $data);



                return $updated

                    ? ['status' => true, 'message' => 'FAQ updated successfully']

                    : ['status' => false, 'message' => 'Failed to update FAQ'];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while updating FAQ'];

        }

    }



    public function deleteFaq($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                $faq = $this->faqRepository->find($id);

                if (!$faq) {

                    return ['status' => false, 'message' => 'FAQ not found'];

                }



                $deleted = $this->faqRepository->deleteData(['id' => $id]);



                return $deleted

                    ? ['status' => true, 'message' => 'FAQ deleted successfully']

                    : ['status' => false, 'message' => 'Failed to delete FAQ'];

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return ['status' => false, 'message' => 'Something went wrong while deleting FAQ'];

        }

    }



    public function getFaqDetail($id)

    {

        try {

            return $this->handleServiceCall(function () use ($id) {

                return $this->faqRepository->find($id);

            }, null); // Return null on error for this method

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }

}

