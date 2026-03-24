<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Repositories\Eloquent\UserRepository;

class ValidOtp implements Rule
{
    protected $phoneCode;
    protected $phoneNumber;
    protected $userRepo;

    public function __construct($phoneCode, $phoneNumber)
    {
        $this->phoneCode = $phoneCode;
        $this->phoneNumber = $phoneNumber;
        $this->userRepo = app(UserRepository::class);
    }

    public function passes($attribute, $value)
    {
        $user = $this->userRepo->getByWhere([
            'phone_code' => $this->phoneCode,
            'phone_number' => $this->phoneNumber
        ], [], ['*'], [], [], 'first');
        
        if (!$user) {
            return false;
        }

        // Check if OTP matches
        return $user->otp == $value;
    }

    public function message()
    {
        return __('validation.custom.otp.invalid');
    }
}
