<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\ContactUsRequest;
use App\Contracts\Services\ContactUsServiceInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class ContactUsController extends BaseController
{
    protected $contactUsService;

    public function __construct(ContactUsServiceInterface $contactUsService)
    {
        $this->contactUsService = $contactUsService;
    }

    public function store(ContactUsRequest $request)
    {
        try {
            $contact = $this->contactUsService->store($request->validated());
            return $this->sendResponse($contact, __('message.contact_message_submitted_successfully'));
        } catch (Exception $e) {
            Log::error("Error in " . __CLASS__ . "::" . __FUNCTION__ . ": " . $e->getMessage());
            return $this->sendError(__('message.failed_to_submit_contact_message'), [], 500);
        }
    }
}
