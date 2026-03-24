<?php



namespace App\Http\Controllers;



use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Foundation\Bus\DispatchesJobs;

use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Support\Facades\Log;



class BaseController extends Controller

{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



    /**

     * Send a success response.

     */

    protected function sendResponse($result, $message = '', $code = 200,$status = 1)

    {

        try {

            return response()->json([

                'status' => $status,

                'data'    => $result,

                'message' => $message,

                'error' => new \stdClass(),

            ], $code);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([

                'status' => 0,

                'message' => $e->getMessage(),

                'status'  => 500,

                'error' => new \stdClass()

            ], 500);

        }

    }



    /**

     * Send an error response.

     */

    protected function sendError($error, $errorMessages = [], $code = 400)

    {

        try {

            $response = [

                'success' => 0,

                'message' => $error,

                'status'  => $code,

                'error_code' => $code

            ];



            if (!empty($errorMessages)) {

                $response['data'] = $errorMessages;

            }



            return response()->json($response, $code);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([

                'success' => false,

                'message' => 'Something went wrong',

                'status'  => 500,

                'error_code' => 500

            ], 500);

        }

    }



    /**

     * Get the authenticated user or return an error response.

     */

    protected function getAuthenticatedUserOrError($request)

    {

        try {

            $user = $request->user();

            if (!$user) {

                return $this->sendError(__('message.user_not_authenticated'), [], 401);

            }

            return $user;

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return $this->sendError(__('message.authentication_check_failed'), [], 500);

        }

    }



    /**

     * Send an admin success response.

     */

    protected function adminSuccessResponse($data = [], $message = 'Success', $status = 1, $httpCode = 200)

    {

        try {

            return response()->json([

                'status' => $status,

                'message' => $message,

                'data' => $data

            ], $httpCode);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([

                'status' => 0,

                'message' => 'Something went wrong',

                'data' => []

            ], 500);

        }

    }



    /**

     * Send an admin error response.

     */

    protected function adminErrorResponse($message = 'Error', $data = [], $errors = [], $status = 0, $httpCode = 422)

    {

        try {

            $response = [

                'status' => $status,

                'message' => $message,

                'data' => $data

            ];
            

            if (!empty($errors)) {

                $response['errors'] = $errors;

            }

            

            return response()->json($response, $httpCode);

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

            return response()->json([

                'status' => 0,

                'message' => 'Something went wrong',

                'data' => []

            ], 500);

        }

    }

}

