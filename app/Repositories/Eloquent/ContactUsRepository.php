<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContactUsRepositoryInterface;
use App\Models\ContactUs;
use Exception;
use Illuminate\Support\Facades\Log;

class ContactUsRepository extends BaseRepository implements ContactUsRepositoryInterface
{
    protected $model;

    public function __construct(ContactUs $model)
    {
        $this->model = $model;
    }

    /**
     * Get All Contact Data With User Relation
     */
    public function getAllData()
    {
        try {

            return $this->model
                ->with([
                    'user'
                ])
                ->get();

        } catch (Exception $e) {

            Log::error('ContactUsRepository@getAllData Error : '.$e->getMessage());

            return collect();
        }
    }

    /**
     * Update Status
     */
    public function updateStatus(array $byWhere, array  $data)
    {
        try {

            return $this->model
                ->where($byWhere)
                ->update($data);

        } catch (Exception $e) {

            Log::error('ContactUsRepository@updateStatus Error : '.$e->getMessage());

            return false;
        }
    }
}