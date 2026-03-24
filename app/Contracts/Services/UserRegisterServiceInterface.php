<?php



namespace App\Contracts\Services;



interface UserRegisterServiceInterface

{

    public function register(array $data);

    public function verifyOtp(array $data);

    public function completeProfile(array $data);

    public function uploadImages(array $images);

    public function uploadSingleImage($image);
    public function updateLocationConsent($userId, $locationConsent);

    public function addLocation($userId, $location, $latitude, $longitude);

    public function getUserDetail($userId);
    public function editProfile($userId, array $data);
}

