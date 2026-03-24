<?php

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface AdminContentServiceInterface
{
    public function getAllContents();
    public function updateContent(Request $request, $id);
    public function getContentDetail($id);
}
