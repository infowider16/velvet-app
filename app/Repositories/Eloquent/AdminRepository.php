<?php

namespace App\Repositories\Eloquent;


use App\Models\Admin;
use App\Contracts\Repositories\AdminRepositoryInterface;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    protected $model;

    public function __construct(Admin $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    public function getOne($byWhere, $column = ['*'])
    {
        try {
            return $this->model->select($column)->where($byWhere)->first();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            throw $e;
        }
    }

    public function getAll()
    {
        // Use BaseRepository's all()
        try {
            return $this->all();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            throw $e;
        }
    }

    public function delete($byWhere)
    {
        // Use BaseRepository's deleteData()
        try {
            return $this->deleteData($byWhere);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            throw $e;
        }
    }

    /**
     * Get admin(s) by where and orderBy, compatible with interface and base repository.
     */
    public function getByWhere(
        $byWhere = [],
        $orderBy = ['id' => 'desc'],
        $columns = ['*'],
        $relations = [],
        $relationFilters = [],
        $method = 'get'
    ) {
        try {
            return parent::getByWhere($byWhere, $orderBy, $columns, $relations, $relationFilters, $method);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return false;
        }
    }

    public function createOrUpdate($byWhere, $allData)
    {
        // Use BaseRepository's updateOrCreate()
        try {
            return $this->updateOrCreate($byWhere, $allData);
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            throw $e;
        }
    }
}
