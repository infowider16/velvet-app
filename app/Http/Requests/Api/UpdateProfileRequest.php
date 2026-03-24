<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'nullable|in:male,female,other',
            'interest_id' => 'nullable|array',
            'interest_id.*' => 'integer|exists:interests,id',
            'images' => 'nullable|array',
            'about_me' => 'nullable|string|max:1000',
            'push_notification_status' => 'nullable|boolean',
            'phone_code' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'country_code' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ];
    }

   
}