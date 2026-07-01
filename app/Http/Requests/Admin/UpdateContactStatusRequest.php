<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:contact_us,id',

            'status' => 'required|in:Pending,Open,In Progress,Resolved'
        ];
    }

    public function messages()
    {
        return [

            'id.required' => 'Contact id is required.',

            'id.exists' => 'Selected contact does not exist.',

            'status.required' => 'Status field is required.',

            'status.in' => 'Invalid status selected.'
        ];
    }
}