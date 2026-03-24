<?php

namespace App\Contracts\Repositories;

interface AdminRepositoryInterface
{
    public function create($allData);
    public function update($byWhere, $update);
    public function getOne($byWhere);
    public function getAll();
    public function delete($byWhere);
    public function getByWhere($byWhere, $orderBy = ['id' => 'desc']);
    public function createOrUpdate($byWhere, $allData);
}
