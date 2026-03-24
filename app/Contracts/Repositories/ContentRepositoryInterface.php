<?php

namespace App\Contracts\Repositories;

interface ContentRepositoryInterface
{
    public function getByWhere(
        $byWhere = [],
        $orderBy = ['id' => 'desc'],
        $columns = ['*'],
        $relations = [],
        $relationFilters = [],
        $method = 'get'
    );
    public function find($id);
    public function update(array $where, array $data);
    public function create(array $data);
    public function deleteData(array $modelData);
    
}
