<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContentRepositoryInterface;
use App\Models\Content;

class ContentRepository extends BaseRepository implements ContentRepositoryInterface
{
    protected $model;

    public function __construct(Content $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }
}
