<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Contracts\Repositories\ContactUsRepositoryInterface;
use App\Contracts\Services\ContactUsServiceInterface;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;

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

            $data['user_id'] = auth('api')->id();

           
            $contact = $this->contactUsRepo->create($data);

          
            $mailData = [
                'subject' => 'New Contact Us Request',

                'body' => '
                    <h3>New Contact Us Enquiry</h3>

                    <p><strong>Name:</strong> '.$data['name'].'</p>

                    <p><strong>Email:</strong> '.$data['email'].'</p>

                    <p><strong>Message:</strong></p>

                    <p>'.$data['message'].'</p>
                ',
            ];

           
            Mail::to('deeksha.webwiders@gmail.com')
                ->send(new ContactUsMail($mailData));

            return $contact;

        } catch (Exception $e) {

            Log::error('Contact Us submission failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

}