<?php

namespace App\Contracts\Repositories;

interface BaseRepositoryInterface
{
    public function all($columns = ['*'], $orderBY = ['id' => 'desc']);
    public function getOneData($byWhere);
    public function create($allData);
    public function update($byWhere, $update);
    public function deleteData(array $modelData);
    public function clearAllCache();
    public function find($id, $columns = ['*']);
    public function findOrFail($id, $columns = ['*']);
    public function firstOrCreate(array $attributes, array $values = []);
    public function updateOrCreate($byWhere, $allData);
    public function pluck($column, $key = null);
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null);
    public function getByWhere(
        $byWhere = [],
        $orderBy = ['id' => 'desc'],
        $columns = ['*'],
        $relations = [],
        $relationFilters = [],
        $method = 'get'
    );
}
