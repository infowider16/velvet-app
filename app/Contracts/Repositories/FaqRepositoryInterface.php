<?php

namespace App\Contracts\Repositories;

interface FaqRepositoryInterface 
{
    public function getByWhere(
        $byWhere = [],
        $orderBy = ['id' => 'desc'],
        $columns = ['*'],
        $relations = [],
        $relationFilters = [],
        $method = 'get'
    );
    public function all();
    public function create(array $data);
    public function update(array $data, $id);
    public function deleteData(array $modelData);
    public function find($byWhere);
}
