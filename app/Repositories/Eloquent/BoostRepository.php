<?php


namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BoostRepositoryInterface;
use App\Models\Boost;
use App\Models\BoostHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
class BoostRepository extends BaseRepository
{
    protected $model;
    protected $historyModel;

    public function __construct(
        Boost $model,
        BoostHistory $boostHistory
    ) {

        $this->model = $model;
        $this->historyModel = $boostHistory;
        parent::__construct($model);
    }

    /**
     * Create boost history record.
     */
    public function createHistoryBoost(array $data): BoostHistory
    {
        try {
            return $this->historyModel->create($data);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to create boost history', 0, $e);
        }
    }

    /**
     * Update boost history record.
     */
    public function updateHistoryBoost(int $id, array $data): bool
    {
        try {
            return $this->historyModel
                ->whereKey($id)
                ->update($data) > 0;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to update boost history id {$id}", 0, $e);
        }
    }

    /**
     * Get single boost history by ID.
     */
    public function getBoostHistoryById(
        int $id,
        array $relations = []
    ): ?BoostHistory {
        try {
            return $this->historyModel
                ->with($relations)
                ->find($id);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to get boost history by id {$id}", 0, $e);
        }
    }

    /**
     * Get boost history list by conditions.
     */
    public function getBoostHistoryList(
        array $conditions = [],
        array $relations = []
    ): Collection {
        $query = $this->historyModel->with($relations);

        foreach ($conditions as $field => $value) {
            $query->when(
                is_array($value),
                fn ($q) => $q->whereIn($field, $value),
                fn ($q) => $q->where($field, $value)
            );
        }

        try {
            return $query->get();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to get boost history list', 0, $e);
        }
    }

    public function findActiveBoostHistory(int $userId, Carbon $nowSwiss): ?object
    {
        // If DB stores Swiss local time in DATETIME (no timezone conversion needed)
        return $this->historyModel
            ->where('user_id', $userId)
            ->whereNotNull('start_date_time')
            ->whereNotNull('end_date_time')
            ->where('start_date_time', '<=', $nowSwiss->toDateTimeString())
            ->where('end_date_time', '>=', $nowSwiss->toDateTimeString())
            ->orderByDesc('id')
            ->first();
    }
}
