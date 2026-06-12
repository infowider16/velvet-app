<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Contracts\Repositories\ContactUsRepositoryInterface;
use App\Contracts\Services\ContactUsServiceInterface;

class ContactUsService implements ContactUsServiceInterface
{
    protected ContactUsRepositoryInterface $contactUsRepo;

    public function __construct(ContactUsRepositoryInterface $contactUsRepo)
    {
        $this->contactUsRepo = $contactUsRepo;
    }

    public function store(array $data)
    {
        try {
            $data['user_id'] = auth()->id();
            return $this->contactUsRepo->create($data);
        } catch (Exception $e) {
            Log::error('Contact Us submission failed', [
                'message' => $e->getMessage(),
                'data'    => $data,
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}