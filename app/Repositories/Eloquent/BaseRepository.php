<?php



namespace App\Repositories\Eloquent;



use Illuminate\Support\Facades\Cache;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;



class BaseRepository

{

    protected $model;

    protected $cacheTime = 60;



    public function __construct(Model $model)

    {

        $this->model = $model;

    }



    public function all($columns = ['*'], $orderBY = ['id' => 'desc'])

    {

        try {

            return $this->model->orderBy(key($orderBY), $orderBY[key($orderBY)])->get($columns);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    // public function getOneData($byWhere)

    // {

    //     try {

    //         return $this->model->where($byWhere)->first();

    //     } catch (\Exception $e) {

    //         $this->logError(__FUNCTION__, $e);

    //         return null;

    //     }

    // }

    public function getOneData(array $byWhere, array $withRelations = [])
    {
        try {
            $query = $this->model->where($byWhere);

            // If relationships are specified, eager load them
            if (!empty($withRelations)) {
                $query = $query->with($withRelations);
            }

            return $query->first();
        } catch (\Exception $e) {
            $this->logError(__FUNCTION__, $e);
            return null;
        }
    }





    public function create($allData)

    {

        try {

            

            $model = $this->model->create($allData);

            return $model->fresh();

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    public function update($byWhere, $update)

    {

        try {

            $model = $this->getOneData($byWhere);

            return $model ? $model->update($update) : false;

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return false;

        }

    }



    public function deleteData(array $modelData)

    {

        try {

            $model = $this->getOneData($modelData);

            return $model ? $model->delete() : false;

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return false;

        }

    }



    /**

     * Clear cache method with try-catch

     */

    public function clearAllCache()

    {

        try {

            $table = $this->model->getTable();

            // Add cache clearing logic here if needed

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

        }

    }



    /**

     * Find a model by its primary key.

     */

    public function find($id, $columns = ['*'])

    {

        try {

            return $this->model->find($id, $columns);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    /**

     * Find a model by its primary key or throw an exception.

     */

    public function findOrFail($id, $columns = ['*'])

    {

        try {

            return $this->model->findOrFail($id, $columns);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            throw $e;

        }

    }



    /**

     * Get the first record matching the attributes or create it.

     */

    public function firstOrCreate(array $attributes, array $values = [])

    {

        try {

            return $this->model->firstOrCreate($attributes, $values);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    /**

     * Update an existing model or create a new one.

     */

    public function updateOrCreate($byWhere, $allData)

    {

        try {

            

            // This should return the model instance

            return $this->model->updateOrCreate($byWhere, $allData);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    /**

     * Get a single column's value from all records.

     */

    public function pluck($column, $key = null)

    {

        try {

            return $this->model->pluck($column, $key);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    /**

     * Paginate the given query.

     */

    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)

    {

        try {

            return $this->model->paginate($perPage, $columns, $pageName, $page);

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }



    /**

     * Build where query conditions

     */

    protected function buildWhereQuery($byWhere)

    {

        try {

            return $this->model->where(function ($query) use ($byWhere) {

                foreach ($byWhere as $column => $condition) {

                    if (is_array($condition)) {

                        if ($condition[0] === "IN") {

                            unset($condition[0]);

                            $query->whereIn($column, $condition);

                        } else {

                            $query->where($column, $condition[0], $condition[1]);

                        }

                    } else {

                        $query->where($column, $condition);

                    }

                }

            });

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return $this->model; // fallback: return base query

        }

    }



    /**

     * Build order by string

     */

    protected function buildOrderByString($orderBy)

    {

        try {

            $orderByString = '';

            foreach ($orderBy as $column => $direction) {

                $orderByString .= "$column $direction, ";

            }

            return rtrim($orderByString, ', ');

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return '';

        }

    }



    /**

     * Log errors consistently

     */

    protected function logError($function, $exception)

    {

        try {

            Log::error("Error in " . __CLASS__ . "::" . $function . ": " . $exception->getMessage());

        } catch (\Exception $e) {

            // Optionally, you can handle logging failure here

        }

    }



    /**

     * Get model(s) by dynamic where, columns, relations, relation filters, and method.

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

            $query = $this->model->query();



            if (!empty($byWhere)) {

                $query->where($byWhere);

            }



            if (!empty($relationFilters)) {

                foreach ($relationFilters as $relationName => $filters) {

                    $query->whereHas($relationName, function ($q) use ($filters) {

                        foreach ($filters as $column => $value) {

                            $q->where($column, $value);

                        }

                    });

                }

            }



            if (!empty($relations)) {

                $query->with($relations);

            }



            // Apply orderBy if present

            if (!empty($orderBy)) {

                foreach ($orderBy as $col => $dir) {

                    $query->orderBy($col, $dir);

                }

            }



            if ($method === 'get') {

                return $query->get($columns);

            } elseif ($method === 'first') {

                return $query->first($columns);

            } elseif ($method === 'count') {

                return $query->count();

            } else {

                throw new \InvalidArgumentException("Invalid method: $method");

            }

        } catch (\Exception $e) {

            $this->logError(__FUNCTION__, $e);

            return false;

        }

    }



    /**

     * Get data with pagination, where conditions, relations, and ordering

     */

    public function getDataWithPagination(

        $whereConditions = [],

        $relations = [],

        $columns = ['*'],

        $relationFilters = [],

        $orderBy = ['id' => 'desc'],

        $perPage = 15,

        $page = null,
        array $scopes = []

    ) {

        try {

            $query = $this->model->query();

             // ✅ Apply model scopes (optional)
            foreach ($scopes as $scope) {
               
                if (is_array($scope)) {
                    // ['scopeName', param1, param2...]
                    $query->{$scope[0]}(...array_slice($scope, 1));
                } elseif (is_string($scope)) {
                    // 'active'
                    $query->{$scope}();
                }
            }

            // Apply where conditions

            if (!empty($whereConditions)) {

                
                foreach ($whereConditions as $condition) {

                    if ($condition instanceof \Closure) {
                        $query->where($condition);
                        continue;
                    }
                    if (is_array($condition) && count($condition) === 3) {

                        // Handle special 'IN' operator for multiple values

                        if (strtoupper($condition[1]) === 'IN') {

                            $query->whereIn($condition[0], $condition[2]);

                        } else {

                            $query->where($condition[0], $condition[1], $condition[2]);

                        }

                    } elseif (is_array($condition) && count($condition) === 2) {

                        $query->where($condition[0], $condition[1]);

                    }

                }

            }



            // Apply relation filters

            if (!empty($relationFilters)) {

                foreach ($relationFilters as $relationName => $filters) {

                    $query->whereHas($relationName, function ($q) use ($filters) {

                        foreach ($filters as $column => $value) {

                            $q->where($column, $value);

                        }

                    });

                }

            }



            // Apply relations

            if (!empty($relations)) {

                $query->with($relations);

            }



            // Apply ordering with support for DB::raw (for random ordering)

            if (!empty($orderBy)) {

                foreach ($orderBy as $col => $dir) {

                    if (is_object($col) && get_class($col) === 'Illuminate\Database\Query\Expression') {

                        // Handle DB::raw for random ordering

                        $query->orderByRaw($col->getValue());

                    } else {

                        $query->orderBy($col, $dir);

                    }

                }

            }

                // dd($query->toSql(), $query->getBindings());

            return $query->paginate($perPage, $columns, 'page', $page);

        } catch (\Exception $e) {
            dd($e);
            $this->logError(__FUNCTION__, $e);

            return null;

        }

    }

}