<?php

namespace App\Contracts\Repositories;

interface ContactUsRepositoryInterface
{
    public function create(array $data);
    public function all();
    public function find($id);
    public function deleteData(array $modelData);
}
