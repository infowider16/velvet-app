<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Repositories\Eloquent\UserRepository;
use Carbon\Carbon;

class ValidOtpNotExpired implements Rule
{
    protected $phoneCode;
    protected $phoneNumber;
    protected $countryCode;
    protected $userRepo;
    protected $errorMessage;

    public function __construct($phoneCode, $phoneNumber, $countryCode)
    {
        $this->phoneCode = $phoneCode;
        $this->phoneNumber = $phoneNumber;
        $this->countryCode = $countryCode;

        $this->userRepo = app(UserRepository::class);

        // default message
        $this->errorMessage = __('validation.custom.otp.invalid');
    }

    public function passes($attribute, $value)
    {
        $user = $this->userRepo->getByWhere([
            'phone_code' => $this->phoneCode,
            'phone_number' => $this->phoneNumber,
            'country_code' => $this->countryCode
        ], [], ['*'], [], [], 'first');

        if (!$user) {
            $this->errorMessage = __('validation.custom.otp.user_not_found');
            return false;
        }

        // OTP mismatch
        if ($user->otp != $value) {
            $this->errorMessage = __('validation.custom.otp.invalid');
            return false;
        }

        // Expiry missing
        if (empty($user->expired_at)) {
            $this->errorMessage = __('validation.custom.otp.expired');
            return false;
        }

        $expiredAt = Carbon::parse($user->expired_at);

        if (now()->greaterThan($expiredAt)) {
            $this->errorMessage = __('validation.custom.otp.expired');
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->errorMessage;
    }
}