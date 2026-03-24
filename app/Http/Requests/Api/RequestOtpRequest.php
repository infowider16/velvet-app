<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\UniquePhoneCodeNumber;

class RequestOtpRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => [
                'required',
                'string',
                new UniquePhoneCodeNumber()
            ],
            'phone_code'   => 'required|string',
            'country_code' => 'nullable|string',
        ];
    }

   
}