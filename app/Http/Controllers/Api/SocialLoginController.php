<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\SocialLoginRequest;
use App\Contracts\Services\SocialLoginServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends BaseController
{
    protected $socialLoginService;

    public function __construct(SocialLoginServiceInterface $socialLoginService)
    {
        $this->socialLoginService = $socialLoginService;
    }

    public function socialLogin(SocialLoginRequest $request)
    {
        try {
            $data = $request->validated();
            $result = $this->socialLoginService->socialLogin($data);
            return $this->sendResponse($result, __('message.social_login_successful'));
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::socialLogin: " . $e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
