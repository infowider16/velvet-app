<?php

namespace App\Http\Requests\Api;

class ContactUsRequest extends BaseApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string|max:2000',
            'subject' => 'nullable|string|max:255',
            'image'   => 'nullable|string|max:255',
        ];
    }
}