<?php



namespace App\Services;



use Illuminate\Support\Facades\Log;



class BaseService

{

    /**

     * Log errors consistently

     */

    protected function logError($function, $exception)

    {

        try {

            Log::error("Error in " . static::class . "::" . $function . ": " . $exception->getMessage());

        } catch (\Exception $e) {

            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());

        }

    }



    /**

     * Return a success response array

     */

    protected function successResponse($message, $data = [])

    {

        try {

            return [

                'status' => true,

                'message' => $message,

                'data' => $data

            ];

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return [

                'status' => false,

                'message' => __('message.something_went_wrong_1'),

                'data' => []

            ];

        }

    }



    /**

     * Return an error response array

     */

    protected function errorResponse($message, $data = [])

    {

        try {

            return [

                'status' => false,

                'message' => $message,

                'data' => $data

            ];

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return [

                'status' => false,

                'message' => __('message.something_went_wrong_1'),

                'data' => []

            ];

        }

    }



    /**

     * Handle try-catch blocks consistently

     */

    protected function handleServiceCall(callable $callback, $errorMessage = 'Something went wrong')

    {

        try {

            return $callback();

        } catch (\Exception $e) {

            $this->logError(debug_backtrace()[1]['function'], $e);

            

            if (is_string($errorMessage)) {

                return $this->errorResponse($errorMessage);

            }

            

            return $errorMessage;

        }

    }



    /**

     * Handle DataTable operations with try-catch

     */

    protected function handleDataTableCall(callable $callback)

    {

        try {

            return $callback();

        } catch (\Exception $e) {

            $this->logError(debug_backtrace()[1]['function'], $e);

            return response()->json([

                'status' => false,

                'message' => __('message.something_went_wrong_1')

            ], 500);

        }

    }



    /**

     * Return an admin success response with proper JSON format

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

            $this->logError(__FUNCTION__, $e);

            return response()->json([

                'status' => 0,

                'message' => __('message.something_went_wrong_1'),

                'data' => []

            ], 500);

        }

    }



    /**

     * Return an admin error response with proper JSON format

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

            $this->logError(__FUNCTION__, $e);

            return response()->json([

                'status' => 0,

                'message' => __('message.something_went_wrong_1'),

                'data' => []

            ], 500);

        }

    }

}

