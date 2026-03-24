<?php

namespace App\Services;

use App\Contracts\Repositories\ContactUsRepositoryInterface;
use App\Contracts\Services\ContactUsServiceInterface;

class ContactUsService implements ContactUsServiceInterface
{
    protected $contactUsRepo;

    public function __construct(ContactUsRepositoryInterface $contactUsRepo)
    {
        $this->contactUsRepo = $contactUsRepo;
    }

    public function store(array $data)
    {
       
        return $this->contactUsRepo->create($data);
    }
}
