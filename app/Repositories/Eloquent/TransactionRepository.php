<?php

namespace App\Repositories\Eloquent;

use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TransactionRepository
{
    protected $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new transaction.
     *
     * @param array $data
     * @return Transaction
     *
     * @throws \Exception
     */
    public function create(array $data): Transaction
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $e) {
            Log::error('Transaction create failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to create transaction.');
        }
    }

    /**
     * Get transactions for DataTable with related user and plan.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getForDataTable()
    {
        try {
            return $this->model->with(['user', 'ghostPlan', 'boostPlan', 'pinPlan'])->orderBy('id', 'DESC');
        } catch (\Exception $e) {
            Log::error('Failed to fetch transactions for DataTable', [
                'error' => $e->getMessage(),
            ]);
            throw new \Exception('Failed to fetch transactions.');
        }
    }

    public function getByWhere(array $byWhere, ?array $orderBy = null)
    {
        try {
            $query = $this->model->where($byWhere)->with(['user', 'ghostPlan', 'boostPlan', 'pinPlan']);

            if ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            }

            return $query->first();
        } catch (\Exception $e) {
            Log::error('Failed to fetch transaction.', [
                'conditions' => $byWhere,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to fetch transaction.');
        }
    }


    public function getAllByWhere(array $byWhere)
    {
        try {
            return $this->model
                ->where($byWhere)
                ->with(['user', 'ghostPlan', 'boostPlan', 'pinPlan'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to fetch transaction.', [
                'conditions' => $byWhere,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Failed to fetch transaction.');
        }
    }

    public function getForDataTableWithFilters(array $filters = [], $query = null)
    {
        try {
            // Use provided query or create new one
            if ($query === null) {
                $query = $this->model->with(['user', 'ghostPlan', 'boostPlan', 'pinPlan']);
            }

            if (!empty($filters['feature_type'])) {
                $featureType = match ($filters['feature_type']) {
                    'Pin' => 0,
                    'Boost' => 1,
                    'Ghost' => 2,
                    default => null
                };
                if ($featureType !== null) {
                    $query->where('type', $featureType);
                }
            }

            if (!empty($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            if (!empty($filters['platform']) && $filters['platform'] !== '') {
                $query->where('platform', (int)$filters['platform']);
            }

            if (!empty($filters['date_range'])) {
                $dateRange = $filters['date_range'];
                // Parse date range format: "YYYY-MM-DD - YYYY-MM-DD"
                if (strpos($dateRange, ' - ') !== false) {
                    list($startDate, $endDate) = explode(' - ', $dateRange);
                    $startDate = trim($startDate);
                    $endDate = trim($endDate);
                    
                    // Add time to end date to include the entire day
                    $query->whereBetween('created_at', [
                        $startDate . ' 00:00:00',
                        $endDate . ' 23:59:59'
                    ]);
                }
            }

            if (!empty($filters['search_term'])) {
                $searchTerm = $filters['search_term'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('user_id', 'like', '%' . $searchTerm . '%')
                      ->orWhere('transaction_id', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                          $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                });
            }

            return $query->orderBy('id', 'DESC');
        } catch (\Exception $e) {
            Log::error('Failed to fetch transactions with filters', [
                'error' => $e->getMessage(),
                'filters' => $filters
            ]);
            throw new \Exception('Failed to fetch transactions.');
        }
    }
}
