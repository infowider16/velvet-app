<?php

namespace App\Contracts\Repositories;
interface GroupRepositoryInterface
{

    public function addMemberToGroup($allData);
    // Define any additional methods specific to the Pin repository
    
    public function whereData($byWhere);
}