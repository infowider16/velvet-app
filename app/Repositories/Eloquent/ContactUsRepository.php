<?php



namespace App\Repositories\Eloquent;



use App\Contracts\Repositories\ContactUsRepositoryInterface;

use App\Models\ContactUs;



class ContactUsRepository extends BaseRepository implements ContactUsRepositoryInterface

{

    protected $model;



    public function __construct(ContactUs $model)

    {

        $this->model = $model;

    }

    



}

