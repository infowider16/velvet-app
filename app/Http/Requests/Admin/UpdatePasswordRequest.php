<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::guard('admin')->check();
    }

    public function rules()
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'Current password is required',
            'new_password.required' => 'New password is required',
            'new_password.min' => 'New password must be at least 6 characters',
            'new_password.confirmed' => 'New password confirmation does not match',
            'new_password_confirmation.required' => 'Please confirm your new password',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $admin = Auth::guard('admin')->user();
            if ($admin && !Hash::check($this->input('current_password'), $admin->password)) {
                $validator->errors()->add('current_password', 'Current password is incorrect');
            }
        });
    }
}
