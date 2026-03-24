<?php

namespace App\Contracts\Repositories;

interface InterestRepositoryInterface
{
    public function getParentInterests();
    public function getSubInterests();
}