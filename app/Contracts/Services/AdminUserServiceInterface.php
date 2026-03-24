<?php

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface AdminUserServiceInterface
{
    public function getUserListDataTable();
    public function toggleUserStatus(Request $request);
    public function getUserDetail($id);
}
