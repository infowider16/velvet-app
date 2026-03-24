<?php

namespace App\Http\Requests\Api;

use App\Rules\ValidOtpNotExpired;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOtpRequest extends BaseApiRequest
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
                'exists:users,phone_number',
            ],
            'phone_code' => 'required|string|exists:users,phone_code',
            'country_code' => 'required|string|exists:users,country_code',
            'otp' => [
                'required',
                'digits:6',
                new ValidOtpNotExpired(
                    $this->input('phone_code'),
                    $this->input('phone_number'),
                    $this->input('country_code')
                ),
            ],
        ];
    }

   
}