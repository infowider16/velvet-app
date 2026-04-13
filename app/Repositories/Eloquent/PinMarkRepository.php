<?php

namespace App\Repositories\Eloquent;

use App\Models\{PinMark,Block};
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PinMarkRepository
{
    protected $model;

    public function __construct(PinMark $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return PinMark
     *
     * @throws \Exception
     */
    public function create(array $data): PinMark
        {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error('PinMark create failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to create transaction.');
        }
    }
    
    public function fetch(array $filters = [])
    {
        $userId = getUserId() ?? 0;

        // Users blocked by current user
        $blockedUserIds = Block::where('blocker_id', $userId)
            ->pluck('blocked_id')
            ->toArray();


        // Users who blocked current user
        $blockedByOthers = Block::where('blocked_id', $userId)
            ->pluck('blocker_id')
            ->toArray();

        // Merge both so neither side can see each other
        $hiddenUserIds = array_unique(array_merge($blockedUserIds, $blockedByOthers));

        $swissTime = $filters['swiss_time'] ?? null;  // client sends Swiss time
        $hours     = (int)($filters['hours'] ?? 48);

        $query = $this->model->newQuery()->withinHoursSwiss($swissTime, $hours);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['country_code'])) {
            $countryCodes = array_map(
                'trim',
                explode(',', $filters['country_code'])
            );
            $query->whereIn('country_code', $countryCodes);
        }

        if (count($hiddenUserIds) > 0) {
            $query->whereNotIn('user_id', $hiddenUserIds);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', 1);
        }

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


}
