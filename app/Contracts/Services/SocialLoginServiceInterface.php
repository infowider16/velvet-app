<?php

namespace App\Contracts\Services;

interface SocialLoginServiceInterface
{
    public function socialLogin(array $data);
}
