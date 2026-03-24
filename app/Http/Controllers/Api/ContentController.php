<?php



namespace App\Http\Controllers\Api;



use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Services\ContentService;

use Exception;

use Illuminate\Support\Facades\Log;



class ContentController extends BaseController

{

    protected $contentService;



    public function __construct(ContentService $contentService)

    {

        $this->contentService = $contentService;
    }



    public function show(Request $request)

    {

        try {

            $slug = $request->query('slug');

            if (!$slug) {

                return $this->sendError(__('message.slug_is_required'), [], 422);
            }



            $content = $this->contentService->getContentBySlug($slug);
           


            if (!$content) {

                return $this->sendError(__('message.content_not_found'), [], 404);
            }
            return $this->sendResponse($content, __('message.content_fetched_successfully'));
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }



    public function faq()

    {

        try {

            $faqs = $this->contentService->getAllActiveFaqs();

            return $this->sendResponse($faqs, __('message.f_a_q_list_fetched_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getInterests()

    {

        try {

            $interests = $this->contentService->getAllInterests();

            return $this->sendResponse($interests, __('message.interests_fetched_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError(__('message.failed_to_fetch_interests') . ': ' . $e->getMessage(), [], 500);
        }
    }

    public function getSubInterests($interestId)

    {

        try {

            $subInterests = $this->contentService->getSubInterestsByParentId($interestId);

            if ($subInterests->isEmpty()) {


                return $this->sendError(__('message.no_subinterests_available_for_the_selected_interest'), [], 404);
              
            }

            return $this->sendResponse($subInterests, __('message.subinterests_fetched_successfully'));
        } catch (Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
