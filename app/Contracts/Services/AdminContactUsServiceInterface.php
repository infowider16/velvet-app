<?php

namespace App\Contracts\Services;

interface AdminContactUsServiceInterface
{
    public function getContactListDataTable();
    public function changeStatus(array $data);
 
}
