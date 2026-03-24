<?php

namespace App\Contracts\Services;

interface AuthServiceInterface
{
    public function login($request);
    public function updatePassword($request);
    public function update($request);
    public function forgetPassword($request);
    public function updateContent($request);
    public function getNotifications($request);
    public function clearNotifications($request, $userdata);
}
