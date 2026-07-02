<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
   
    public function rules(): array
    {
       
        return [
            'user_id' => 'required|exists:users,id',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required',

            'image.required' => 'Please upload an image',
            'image.image' => 'Invalid image file',
            'image.mimes' => 'Image must be a file of type: jpg, jpeg, png, webp',
            'image.max' => 'Image size must not exceed 5MB',
            'image.uploaded' => 'Image upload failed. File may be too large.',
        ];
    }
}