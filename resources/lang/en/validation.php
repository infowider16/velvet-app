<?php

return [
    'digits' => 'The :attribute must be :digits digits.',
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute must be a valid email address.',
    'password_incorrect' => 'The old password is incorrect.',
    'same' => 'The :attribute and :other must match.',
    'confirmed' => 'The password confirmation does not match.',
    'string' => 'The :attribute must be a string.',
    'array' => 'The :attribute must be an array.',
    'date' => 'The :attribute is not a valid date.',
    'in' => 'The selected :attribute is invalid.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'boolean' => 'The :attribute field must be true or false.',
    'unique' => 'The :attribute has already been taken.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'numeric' => 'The :attribute must be a number.',
    'integer' => 'The :attribute must be an integer.',
    'exists' => 'The :attribute must be a valid ID.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
    ],

    'first_name' => 'First Name',
    'last_name' => 'Last Name',
    'refferal_code' => 'The referral code you entered is invalid. Please check and try again.',
    'required_without' => 'The :attribute field is required.',
    'required_with' => 'The :attribute field is required with :other.',

    'custom' => [
        'store_owner_name' => [
            'required' => 'The Store Owner field is required.',
        ],
        'store_name' => [
            'required' => 'The Store Name field is required.',
        ],
        'registration_no' => [
            'required' => 'The Registration No field is required.',
            'unique' => 'The Registration No is already taken.',
        ],
        'document_proof' => [
            'required' => 'The Document Proof is required.',
        ],
        'phone_no' => [
            'required' => 'The Phone No field is required.',
            'unique' => 'The Phone No is already taken.',
        ],
        'email' => [
            'required' => 'The Email field is required.',
            'unique' => 'The Email is already taken.',
        ],
        'password' => [
            'required' => 'The Password field is required.',
            'min' => 'The Password must be at least :min characters.',
        ],
        'address' => [
            'required' => 'The Address field is required.',
        ],
        'old_password' => [
            'required' => 'The Old Password field is required.',
        ],
        'new_password' => [
            'required' => 'The New Password field is required.',
            'min' => 'The New Password must be at least :min characters long.',
        ],
        'full_name' => [
            'required' => 'The Full Name field is required.',
        ],
        'message' => [
            'required' => 'The Message field is required.',
        ],
        'amount' => [
            'required' => 'The Amount field is required.',
            'numeric' => 'The Amount must be a number.',
        ],
        'receiver_number' => [
            'required' => 'The Receiver Number field is required.',
        ],
        'confirm_password' => [
            'required' => 'Confirm password is required.',
            'same' => 'Confirm password must match the new password.',
            'required_with' => 'Confirm password is required when new password is present.',
        ],
        'user_id' => [
            'invalid_user_id' => 'User ID :id does not exist.',
            'required' => 'User ID is required.',
        ],
        'group_id' => [
            'required' => 'Group ID is required.',
            'integer' => 'Group ID must be an integer.',
            'exists' => 'Group does not exist.',
        ],
        'role' => [
            'in' => 'Role must be either member or admin.',
        ],
        'phone_number' => [
            'required' => 'The phone number field is required.',
            'string'   => 'The phone number must be a string.',
            'unique_combination' => 'The combination of phone code and phone number has already been registered.',
        ],
        'phone_code' => [
            'required' => 'The phone code field is required.',
            'string' => 'The phone code must be a string.',
        ],
        'country_code' => [
            'string' => 'The country code must be a string.',
        ],
        'name' => [
            'required' => 'The name is required.',
            'string' => 'The name must be a string.',
            'max' => 'The name may not be greater than :max characters.',
            'unique' => 'The name has already been taken.',
        ],
        'date_of_birth' => [
            'date' => 'Date of birth must be a valid date.',
            'before_or_equal' => 'You must be at least 18 years old.',
        ],
        'gender' => [
            'in' => 'Gender must be male, female, or other.',
        ],
        'interest_id' => [
            'array' => 'Interests must be provided as an array.',
        ],
        'interest_id.*' => [
            'integer' => 'Each interest ID must be an integer.',
            'exists' => 'The selected interest ID is invalid.',
        ],
        'about_me' => [
            'max' => 'About me must not exceed 1000 characters.',
        ],
        'latitude' => [
            'between' => 'Latitude must be between -90 and 90.',
        ],
        'longitude' => [
            'between' => 'Longitude must be between -180 and 180.',
        ],
        'location' => [
            'string' => 'Location must be a string.',
            'max' => 'Location must not exceed 255 characters.',
        ],
        'subject' => [
            'string' => 'Subject must be a string.',
            'max' => 'Subject may not be greater than 255 characters.',
        ],
        'image' => [
            'image' => 'The file must be an image.',
            'mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg.',
            'max' => 'The image may not be greater than 2MB.',
        ],
        'gmail_id' => [
            'required' => 'Email is required.',
            'email' => 'Email must be a valid email address.',
            'max' => 'Email may not be greater than :max characters.',
        ],
        'google_id' => [
            'required' => 'Google ID is required.',
            'string' => 'Google ID must be a string.',
            'max' => 'Google ID may not be greater than :max characters.',
        ],
        'lat' => [
            'numeric' => 'Latitude must be a number.',
        ],
        'lng' => [
            'numeric' => 'Longitude must be a number.',
        ],
        'images' => [
            'required' => 'At least one image is required.',
            'array' => 'Images must be sent as an array.',
            'min' => 'You must upload at least one image.',
        ],

        'images.*' => [
            'required' => 'Each image is required.',
            'image' => 'Each file must be a valid image.',
            'mimes' => 'Each image must be of type: jpeg, png, jpg, gif, or svg.',
            'max' => 'Each image must not be larger than 2MB.',
        ],
        'phone_number' => [
            'required' => 'Phone number is required.',
            'string' => 'Phone number must be a string.',
            'exists' => 'The provided phone number is not registered.',
        ],

        'phone_code' => [
            'required' => 'Phone code is required.',
            'string' => 'Phone code must be a string.',
            'exists' => 'The provided phone code is not valid.',
        ],

        'country_code' => [
            'required' => 'Country code is required.',
            'string' => 'Country code must be a string.',
            'exists' => 'The provided country code is not valid.',
        ],

        'otp' => [
            'required' => 'OTP is required.',
            'digits' => 'OTP must be exactly 6 digits.',
            'invalid' => 'Invalid OTP.',
            'expired' => 'OTP has expired.',
            'user_not_found' => 'User not found.',
        ],
        'location_consent' => [
            'required' => 'Location consent is required.',
            'boolean' => 'Location consent must be true or false.',
        ],
        
    ],

    'attributes' => [
        'store_owner_name' => 'Store Owner',
        'store_name' => 'Store Name',
        'registration_no' => 'Registration No',
        'document_proof' => 'Document Proof',
        'phone_no' => 'Phone No',
        'email' => 'Email Address',
        'password' => 'Password',
        'address' => 'Address',
        'old_password' => 'Old Password',
        'new_password' => 'New Password',
        'full_name' => 'Full Name',
        'message' => 'Message',
        'amount' => 'Amount',
        'receiver_number' => 'Receiver Number',
        'location' => 'Location',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'group_id' => 'Group ID',
        'user_id' => 'User ID',
        'role' => 'Role',
        'name' => 'Name',
        'date_of_birth' => 'Date of Birth',
        'gender' => 'Gender',
        'interest_id' => 'Interest',
        'about_me' => 'About Me',
        'images' => 'Images',
        'subject' => 'Subject',
        'image' => 'Image',
        'phone_number' => 'Phone Number',
        'phone_code' => 'Phone Code',
        'country_code' => 'Country Code',
        'gmail_id' => 'Email',
        'google_id' => 'Google ID',
        'lat' => 'Latitude',
        'lng' => 'Longitude',
        'push_notification_status' => 'Push Notification Status',
        'images' => 'Images',
        'images.*' => 'Image',
        'otp' => 'OTP',
        'location_consent' => 'Location Consent',
    ],
];
