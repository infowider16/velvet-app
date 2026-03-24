<?php

namespace App\Contracts\Services;

use Illuminate\Http\Request;

interface AdminFaqServiceInterface
{
    public function getFaqListDataTable();
    public function createFaq(Request $request);
    public function updateFaq(Request $request, $id);
    public function deleteFaq($id);
    public function getFaqDetail($id);
    
}
