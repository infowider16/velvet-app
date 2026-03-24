<?php



namespace App\Repositories\Eloquent;



use App\Models\Pin;

use App\Contracts\Repositories\PinRepositoryInterface;



class PinRepository extends BaseRepository implements PinRepositoryInterface

{

    protected $model;



    public function __construct(Pin $model)

    {

        $this->model = $model;

        parent::__construct($model);

    }

    

}

