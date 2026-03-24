<?php

namespace App\Repositories\Eloquent;

use App\Models\PinMarkComment;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PinMarkCommentRepository
{
    protected $model;

    public function __construct(PinMarkComment $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return PinMarkComment
     *
     * @throws \Exception
     */
    public function create(array $data): PinMarkComment
        {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error('PinMarkComment create failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to create transaction.');
        }
    }
    
    public function fetch(array $filters = [])
        {
        $query = $this->model->newQuery();
    
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['pin_mark_id'])) {
            $query->where('pin_mark_id', $filters['pin_mark_id']);
        }
        
    
        // if (!empty($filters['country_code'])) {
        //     // Support: IN,AT,US
        //     $countryCodes = array_map(
        //         'trim',
        //         explode(',', $filters['country_code'])
        //     );
    
        //     $query->whereIn('country_code', $countryCodes);
        // }
    
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }else{ $query->where('status', 1); }
    
        $query->orderBy('commented_on', 'desc');
    
        if (!empty($filters['per_page'])) {
            return $query->paginate((int) $filters['per_page']);
        }
    
        return $query->get();
    }
    
    
    public function softDeleteById(int $id): bool
        {
        $mark = $this->model->find($id);
    
        if (!$mark) {
            return false;
        }
    
        return $mark->update([
            'status' => 0
        ]);
    }


}
