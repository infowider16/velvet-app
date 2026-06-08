<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Contracts\Repositories\PlanRepositoryInterface;
use App\Repositories\Eloquent\BoostRepository;
use App\Contracts\Repositories\PinRepositoryInterface;

use App\Services\HomeService;



class HomeController extends BaseController

{

    protected $homeService;
    protected $planRepository;
    protected $boostRepository;
    protected $pinRepository;



    public function __construct(HomeService $homeService,
        PlanRepositoryInterface $planRepository,
        BoostRepository $boostRepository,
        PinRepositoryInterface $pinRepository
    )

    {

        $this->homeService = $homeService;
        $this->planRepository = $planRepository;
        $this->boostRepository = $boostRepository;
        $this->pinRepository = $pinRepository;
    }



    /**

     * Get users for home screen with filters

     *

     * @param Request $request

     * @return JsonResponse

     */

    public function getHomeUsers(Request $request): JsonResponse

    {
       

        try {


            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;
            }

            $filters = [

                'gender' => $request->get('gender', 'all'), // all, male, female

                'min_age' => (int) $request->get('min_age', 18),

                'max_age' => (int) $request->get('max_age', 60),

                'country_code' => $request->get('country_code'), // Can be string or array: IN, US, etc. or [IN, US, CH]

                'sort_by' => $request->get('sort_by', 'distance'), // distance, age, recent

                'random' => (int) $request->get('random', 0), // 1 for random order, 0 for normal

                'page' => (int) $request->get('page', 1),

                'per_page' => $request->has('per_page') ? (int) $request->get('per_page') : null

            ];



            $result = $this->homeService->getHomeUsers($filters,$user->id);



            return $this->sendResponse($result['data'], $result['message']);
        } catch (Exception $e) {

            Log::error('Home users fetch failed: ' . $e->getMessage());

            return $this->sendError(__('message.failed_to_fetch_users'), [], 500);
        }
    }



    /**

     * Get users for map screen with distance-based filters

     *

     * @param Request $request

     * @return JsonResponse

     */

    public function getMapUsers(Request $request): JsonResponse

    {

        try {

            $user = $this->getAuthenticatedUserOrError($request);

            if ($user instanceof JsonResponse) {

                return $user;

            }



            $filters = [

                'gender' => $request->get('gender', 'all'), // all, male, female

                'min_age' => (int) $request->get('min_age', 18),

                'max_age' => (int) $request->get('max_age', 60),

                'min_distance' => (float) $request->get('min_distance', 0), // in km

                'max_distance' => (float) $request->get('max_distance', 1000), // in km

                'order_by' => $request->get('order_by', 'nearest'), // nearest, age, recent

                'page' => (int) $request->get('page', 1),

                'per_page' => (int) $request->get('per_page', 20),

                'user_lat' => (float) $user->lat,

                'user_lng' => (float) $user->lng

            ];



            $result = $this->homeService->getMapUsers($filters, $user->id);



            return $this->sendResponse($result['data'], $result['message']);

        } catch (Exception $e) {

            Log::error('Map users fetch failed: ' . $e->getMessage());

            return $this->sendError(__('message.failed_to_fetch_map_users'), [], 500);

        }

    }

    public function getGhostPlans(): JsonResponse

    {

        try {
          
            $plans = $this->planRepository->getByWhere();
            $plans = $this->planRepository->getByWhere();
            if(empty($plans)){
                $plans = [];
            }
           return $this->sendResponse($plans, __('message.ghost_plans_retrieved_successfully'));

        } catch (Exception $e) {
            Log::error(__('message.failed_to_fetch_ghost_plans') . $e->getMessage());
           return $this->sendError(__('message.failed_to_fetch_ghost_plans'), [], 500);

        }

    }

    public function getBoostPlans(): JsonResponse

    {

        try {

            $plans = $this->boostRepository->getByWhere();

            if(empty($plans)) {

                $plans = [];

            }
            return $this->sendResponse($plans, __('message.boost_plans_retrieved_successfully'));

        } catch (Exception $e) {

            Log::error(__('message.failed_to_fetch_boost_plans') . $e->getMessage());

            return $this->sendError(__('message.failed_to_fetch_boost_plans'), [], 500);

        }

    }

    public function getPinPlans(): JsonResponse

    {

        try {

            $plans = $this->pinRepository->getByWhere();

            if(empty($plans)) {

                $plans = [];

            }

            return $this->sendResponse($plans, __('message.pin_plans_retrieved_successfully'));

        } catch (Exception $e) {

            Log::error(__('message.failed_to_fetch_pin_plans') . $e->getMessage());
            return $this->sendError(__('message.failed_to_fetch_pin_plans'), [], 500);

        }

    }
    
    public function updateUserPlan(Request $request): JsonResponse
    {
        try {
            $getType=$request->plan_type;
            $updateData=$this->homeService->updateUserPlan($getType);
            return $this->sendResponse([], __('message.plans_updated_successfully'));
        } catch (Exception $e) {
            Log::error('Failed to fetch ghost plans: ' . $e->getMessage());
            return $this->sendError(__('message.failed_to_fetch_ghost_plans_1'), [], 500);

        }

    }

}
