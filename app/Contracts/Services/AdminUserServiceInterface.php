<?php

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface AdminUserServiceInterface
{
    public function getUserListDataTable();
    public function toggleUserStatus(Request $request);
    public function getUserDetail($id);
    public function getUserGroupMembersDataTable(Request $request, $userId);
    public function getUserGroupReportsDataTable(Request $request, $userId);
    public function getUserMessagesDataTable(Request $request, $userId);
    public function deleteUserImage(Request $request);
    public function uploadUserImage(Request $request);
    public function deleteUser($id);
}
