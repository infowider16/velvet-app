<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\FaqRepositoryInterface;
use App\Models\Faq;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface

{
    protected $model;

    public function __construct(Faq $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }
}
