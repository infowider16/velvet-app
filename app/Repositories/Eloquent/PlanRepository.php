<?php



namespace App\Repositories\Eloquent;



use App\Models\GhostManagement;

use App\Contracts\Repositories\PlanRepositoryInterface;



class PlanRepository extends BaseRepository implements PlanRepositoryInterface

{

    protected $model;



    public function __construct(GhostManagement $model)

    {

        $this->model = $model;

        parent::__construct($model);

    }

    

}

