<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChangeReportStatusRequest extends FormRequest
{
    /**
     * Authorize request
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules()
    {
        return [
            'id' => 'required|exists:group_reports,id',

            'status' => 'required|in:Pending,Open,In Progress,Resolved'
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages()
    {
        return [

            'id.required' => 'Report id is required.',

            'id.exists' => 'Selected report does not exist.',

            'status.required' => 'Status field is required.',

            'status.in' => 'Invalid status selected.'
        ];
    }
}