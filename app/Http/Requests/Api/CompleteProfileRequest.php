<?php

namespace App\Http\Requests\Api;

class CompleteProfileRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:' . now()->subYears(18)->format('Y-m-d'),
            'gender' => 'required|in:male,female,other',
            'interest_id' => 'required|array',
            'interest_id.*' => 'integer|exists:interests,id',
            'images' => 'nullable|array',
            'about_me' => 'nullable|string|max:1000',
        ];
    }
}