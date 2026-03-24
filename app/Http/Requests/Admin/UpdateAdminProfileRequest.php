<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuthHelper; // <-- Import the helper

class UpdateAdminProfileRequest extends FormRequest
{
    public function authorize()
    {
        // You can use AuthHelper here
        return isAdminLoggedIn();
    }

    public function rules()
    {
        $adminId = getAdminId();
        
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email,' . $adminId,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
            'current_password' => 'nullable|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please enter a valid email address',
            'email.unique' => 'This email is already taken',
            'profile_image.image' => 'Profile image must be an image file',
            'profile_image.mimes' => 'Profile image must be jpeg, png, jpg, or gif',
            'profile_image.max' => 'Profile image size must not exceed 5MB',
            'current_password.min' => 'Current password must be at least 6 characters',
        ];
    }
}
