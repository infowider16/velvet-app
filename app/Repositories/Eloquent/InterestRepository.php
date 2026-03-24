<?php



namespace App\Repositories\Eloquent;



use App\Contracts\Repositories\InterestRepositoryInterface;

use App\Models\Interest;



class InterestRepository extends BaseRepository implements InterestRepositoryInterface

{

    protected $model;



    public function __construct(Interest $model)

    {

        $this->model = $model;

        parent::__construct($model);

    }



    public function getParentInterests()

    {

        try {

            return $this->model->where('parent_id', 0)->orderBy('name', 'asc')->get();

        } catch (\Exception $e) {

            \Log::error('Error in getParentInterests: ' . $e->getMessage());

            return collect();

        }

    }



    public function getSubInterests()

    {

        try {

            return $this->model->with(['parent' => function($query) {

                $query->select('id', 'name');

            }])->where('parent_id', '!=', 0)->orderBy('name', 'asc')->get();

        } catch (\Exception $e) {

            \Log::error('Error in getSubInterests: ' . $e->getMessage());

            return collect();

        }

    }



   
}

