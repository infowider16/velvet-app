<?php

namespace App\Services;

use App\Repositories\Eloquent\TransactionRepository;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\GhostManagement;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    protected TransactionRepository $transactionRepo;
    protected UserRepositoryInterface $userRepo;

    public function __construct(TransactionRepository $transactionRepo, UserRepositoryInterface $userRepo)
    {
        $this->transactionRepo = $transactionRepo;
        $this->userRepo = $userRepo;
    }



    public function storeTransaction(array $requestDatas)
    {
        try {
            $this->validateRequiredKeys($requestDatas);

            $type   = (int) $requestDatas['type'];
            $userId = (int) $requestDatas['user_id'];
        
            // Normalize/prepare common fields
            $requestDatas['platform'] = $this->resolvePlatform($requestDatas);
            
            // Fetch last transaction by type (needed for subscription extend)
            $lastTransaction = $this->getLastTransactionByType($userId, $type);
            
            // Type specific validation & data preparation
            $plan = null;
            $swissNowFormatted = convertTimezone( Carbon::now(), null,  'Y-m-d H:i:s' );
             $requestDatas['created_at'] = $swissNowFormatted;
            if ($type === 2) {
                $startDateTime=$this->normalizeZurichDateTime($requestDatas['start_time'] ?? null);
                
                $requestDatas['start_time'] = $swissNowFormatted;
                
                $plan = $this->getGhostPlanOrFail((int) $requestDatas['plan_id']);

                    // default end_time from plan duration
                    $requestDatas['end_time'] = $this->calculateGhostEndTime(
                        $startDateTime->format('Y-m-d H:i:s'),
                        $plan->duration
                    );
            }
            
            if ($type === 1) {
                $this->validateBoostCount($requestDatas);
            }
            if ($type === 0) {
                $this->validatePinCount($requestDatas);
            }

          
            // Extend subscription if still active OR update boost count
            if ($type === 2 && isset($lastTransaction->id)) {
                $requestDatas = $this->extendActiveSubscriptionIfNeeded($requestDatas, $lastTransaction, $plan);
            } elseif ($type === 1) {
                $requestDatas = $this->mergeExistingBoostCount($requestDatas, $userId);
            }elseif ($type === 0) {
                $requestDatas = $this->mergeExistingPinCount($requestDatas, $userId);
            }
           
            // Ensure start_time/end_time are stored as DB-friendly strings
            $requestDatas = $this->castDateTimesForStorage($requestDatas);
            // Create transaction
            $transaction = $this->transactionRepo->create($requestDatas);

            // Update user fields based on type
           $this->updateAllPlanMetaInDb();
            $this->updateUserAfterTransaction($transaction);
            return $transaction instanceof \Illuminate\Database\Eloquent\Model
                ? $transaction->fresh()
                : $this->transactionRepo->findByWhere(['id' => $transaction['id'] ?? null]);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            dd($e);
            Log::error(
                __CLASS__ . '::' . __FUNCTION__,
                ['error' => $e->getMessage(), 'data' => $requestDatas]
            );

            return null;
        }
    }

    /**
     * -----------------------
     * Private reusable helpers
     * -----------------------
     */

    private function validateRequiredKeys(array $data): void
    {
        foreach (['transaction_id', 'plan_id', 'user_id', 'type'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                throw ValidationException::withMessages([
                    $field => ucfirst(str_replace('_', ' ', $field)) . ' is required.',
                ]);
            }
        }
    }

    private function validateBoostCount(array $data): void
    {
        if (!isset($data['boost_count']) || (int) $data['boost_count'] <= 0) {
            throw ValidationException::withMessages([
                'boost_count' => 'Boost count is required when type is 1.',
            ]);
        }
    }
    private function validatePinCount(array $data): void
    {
        if (!isset($data['pin_count']) || (int) $data['pin_count'] <= 0) {
            throw ValidationException::withMessages([
                'pin_count' => 'Pin count is required when type is 0.',
            ]);
        }
    }

    private function normalizeZurichDateTime(null|string|Carbon $value): Carbon
    {
        if (empty($value)) {
            throw ValidationException::withMessages([
                'start_time' => 'Start time is required when type is 2.',
            ]);
        }

        return $value instanceof Carbon
            ? $value->copy()->setTimezone('Europe/Zurich')
            : Carbon::parse($value)->setTimezone('Europe/Zurich');
    }

    private function resolvePlatform(array $data): int
    {
        return (int) ($data['app_type'] ?? 0);
    }

    private function getGhostPlanOrFail(int $planId)
    {
        $plan = \App\Models\GhostManagement::find($planId);

        if (!$plan) {
            throw ValidationException::withMessages([
                'plan_id' => 'Invalid plan selected.',
            ]);
        }

        return $plan;
    }

    private function getLastTransactionByType(int $userId, int $type)
    {
        $orderBy = $type === 2 ? ['end_time' => 'DESC'] : ['created_at' => 'DESC'];

        return $this->transactionRepo->getByWhere(
            ['user_id' => $userId, 'type' => $type],
            $orderBy
        );
    }

    private function extendActiveSubscriptionIfNeeded(array $data, $lastTransaction, $plan): array
    {
        $swissNow = convertTimezone(now()); // Carbon in Europe/Zurich
        $defaultTimeZone=defaultTimezone();
        $swissEnd = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $lastTransaction->end_time,
            $defaultTimeZone
        );
        
        // compare
        $isExpired = $swissNow->greaterThanOrEqualTo($swissEnd);
        if (
            !$lastTransaction ||
            empty($lastTransaction->end_time) ||
            $isExpired
        ) {
            return $data;
        }
        
        // Use existing end_time as the new start_time, and extend from that end_time
        
        $newEndTime = $this->addDurationToEndTime($lastTransaction->end_time, $plan->duration);
        $data['end_time']   = $newEndTime;

        return $data;
    }

    private function mergeExistingBoostCount(array $data, int $userId): array
    {
        $user = $this->userRepo->getOneData(['id' => $userId]);

        $data['boost_count'] = ((int) ($data['boost_count'] ?? 0)) + (int) ($user->boost_count ?? 0);

        return $data;
    }
    
    private function mergeExistingPinCount(array $data, int $userId): array
    {
        $user = $this->userRepo->getOneData(['id' => $userId]);

        $data['pin_count'] = ((int) ($data['pin_count'] ?? 0)) + (int) ($user->pin_count ?? 0);

        return $data;
    }

    private function castDateTimesForStorage(array $data): array
    {
        // If start_time is Carbon, convert to string for DB insert
        if (isset($data['start_time']) && $data['start_time'] instanceof Carbon) {
            $data['start_time'] = $data['start_time']->format('Y-m-d H:i:s');
        }

        // end_time might be Carbon or string depending on your helper return
        if (isset($data['end_time']) && $data['end_time'] instanceof Carbon) {
            $data['end_time'] = $data['end_time']->format('Y-m-d H:i:s');
        }
        return $data;
    }

    private function updateUserAfterTransaction($transaction): void
    {
        $type = (int) $transaction->type;

        $updateData = match ($type) {
            2 => ['ghost' => $transaction->id,'gost_expire'=>$transaction->end_time],
            1 => ['boost' => $transaction->id, 'boost_count' => (int) $transaction->boost_count],
            0 => ['pin_transaction_id' => $transaction->id,'pin_count' => (int) $transaction->pin_count],
            default => [],
        };
        

        if (!empty($updateData)) {
            $this->userRepo->update(['id' => $transaction->user_id], $updateData);
        }
    }



    /**
     * Calculate the end time for a Ghost plan based on start time and duration
     * Duration format: "4_hours", "24_hours", "7_days", "30_days"
     * 
     * @param string $startTime
     * @param string $duration
     * @return string
     */
    private function calculateGhostEndTime($startTime, $duration)
    {
        $startDate = Carbon::parse($startTime);
        list($amount, $unit) = explode('_', $duration);
        $amount = (int) $amount;

        if ($unit === 'hours') {
            return $startDate->addHours($amount)->toDateTimeString();
        } elseif ($unit === 'days') {
            return $startDate->addDays($amount)->toDateTimeString();
        }

        // Fallback - just return start time if duration format is invalid
        return $startTime;
    }

    /**
     * Add duration to an existing end time
     * Duration format: "4_hours", "24_hours", "7_days", "30_days"
     * 
     * @param string $endTime
     * @param string $duration
     * @return string
     */
    private function addDurationToEndTime($endTime, $duration)
    {
        $endDate = Carbon::parse($endTime);
        list($amount, $unit) = explode('_', $duration);
        $amount = (int) $amount;

        if ($unit === 'hours') {
            return $endDate->addHours($amount)->toDateTimeString();
        } elseif ($unit === 'days') {
            return $endDate->addDays($amount)->toDateTimeString();
        }
        // Fallback
        return $endTime;
    }

    /**
     * Convert duration like "4_hours" to "4 hours" for display.
     */
    private function humanizeDuration($duration)
    {
        if (!is_string($duration) || $duration === '') {
            return '-';
        }
        // Simple conversion: replace underscore with space
        $label = str_replace('_', ' ', strtolower($duration));
        // Ensure pluralization remains as provided (hours/days)
        // Capitalization not needed inside badge
        return $label;
    }

    public function getTransactionListDataTable($request)
    {
       
        $query = $this->transactionRepo->getForDataTable();
        
        $filters = [];
        if ($request->has('feature_type') && !empty($request->get('feature_type'))) {
            $filters['feature_type'] = $request->get('feature_type');
        }
        if ($request->has('payment_status') && !empty($request->get('payment_status'))) {
            $filters['payment_status'] = $request->get('payment_status');
        }
        if ($request->has('platform') && $request->get('platform') !== null && $request->get('platform') !== '') {
            $filters['platform'] = $request->get('platform');
        }
        if ($request->has('search_term') && !empty($request->get('search_term'))) {
            $filters['search_term'] = $request->get('search_term');
        }
        
        if (!empty($filters)) {
            $query = $this->transactionRepo->getForDataTableWithFilters($filters);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_id', fn($row) => $row->user_id ?? '-')
            ->addColumn('name', fn($row) => $row->user?->name ?? '-')
            ->addColumn('feature_type', function ($row) {
                return match ($row->type) {
                    0 => 'Pin',
                    1 => 'Boost',
                    2 => 'Ghost',
                    default => 'Unknown'
                };
            })
            ->addColumn('plan_details', function ($row) {
                switch ((int) $row->type) {
                    case 0: // Pin
                        $plan = $row->pinPlan;
                        return 'Pins • ' . (($plan->pin_count ?? '0') . ' pins');
                    case 1: // Boost
                        $plan = $row->boostPlan;
                        return trim(($plan->title ?? '-') . ' • ' . ($plan->tag ?? '-'));
                    case 2: // Ghost
                        $plan = $row->ghostPlan;
                        $title = $plan->title ?? '-';
                        $tag = $plan->tag ?? '-';
                        $duration = $plan->duration ?? '-';
                        $durationLabel = $this->humanizeDuration($duration);
                        return '<h5 class="mb-1">' . htmlspecialchars($title) . '</h5>' .
                               '<small class="text-muted">' . htmlspecialchars($tag) . '</small><br>' .
                               '<span class="badge badge-info mt-1">' . htmlspecialchars($durationLabel) . '</span>';
                    default:
                        return 'Unknown';
                }
            })
            ->addColumn('amount', function ($row) {
                $planMeta = $row->plan_meta ? json_decode($row->plan_meta, true) : null;
                switch ((int) $row->type) {
                    case 2: // Ghost
                        $amt = (float) ($planMeta['amount'] ?? 0);
                        $cur = $planMeta['currency'] ?? 'CHF';
                        return number_format($amt, 2) . ' ' . $cur;
                    case 1: // Boost => amount (per boost) * boost_count (from transactions)
                        $unit = (float) ($planMeta['amount'] ?? 0);
                        $count = (int) ($row->boost_count ?? 0);
                        $total = $unit * max($count, 1);
                        return number_format($total, 2) . ' CHF';
                    case 0: // Pin
                        $amt = (float) ($planMeta['amount'] ?? 0);
                        return number_format($amt, 2) . ' CHF';
                    default:
                        return '-';
                }
            })
            ->addColumn('payment_status', function ($row) {
                $statusColors = [
                    'pending' => 'warning',
                    'succeeded' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'info'
                ];
                $status = $row->payment_status ?? 'pending';
                $color = $statusColors[$status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('start_time', function ($row) {
                if ($row->type === 2 && $row->start_time) {
                    return Carbon::parse($row->start_time)->format('Y-m-d H:i:s');
                }
                return '-';
            })
            ->addColumn('end_time', function ($row) {
                if ($row->type === 2 && $row->end_time) {
                    return Carbon::parse($row->end_time)->format('Y-m-d H:i:s');
                }
                return '-';
            })
            ->addColumn('platform', function ($row) {
                if ($row->platform === 0) return 'Android';
                if ($row->platform === 1) return 'iOS';
                return '-';
            })
            ->addColumn('transaction_id', fn($row) => $row->transaction_id ?? '-')
            ->rawColumns(['payment_status', 'plan_details'])
            ->make(true);
    }

    /**
     * Get transaction list filtered by transaction type
     * @param \Illuminate\Http\Request $request
     * @param int $type - Transaction type (0=Pin, 1=Boost, 2=Ghost)
     * @return mixed
     */
    public function getTransactionListByType(\Illuminate\Http\Request $request, int $type)
    {
        $query = $this->transactionRepo->getForDataTable();
        
        // Apply type filter
        $query->where('transactions.type', $type);
        
        // Apply other filters
        $filters = [];
        if ($request->has('payment_status') && !empty($request->get('payment_status'))) {
            $filters['payment_status'] = $request->get('payment_status');
        }
        if ($request->has('platform') && $request->get('platform') !== null && $request->get('platform') !== '') {
            $filters['platform'] = $request->get('platform');
        }
        if ($request->has('search_term') && !empty($request->get('search_term'))) {
            $filters['search_term'] = $request->get('search_term');
        }
        if ($request->has('date_range') && !empty($request->get('date_range'))) {
            $filters['date_range'] = $request->get('date_range');
        }
        
        if (!empty($filters)) {
            // Clone query with relations preserved
            $tempQuery = clone $query;
            $query = $this->transactionRepo->getForDataTableWithFilters($filters, $tempQuery);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_id', fn($row) => $row->user_id ?? '-')
            ->addColumn('name', function ($row) {
                if (!$row->user) return '-';
                $userId = $row->user->id;
                $userName = htmlspecialchars($row->user->name);
                $url = route('admin.user.show', ['id' => $userId]);
                return '<a href="' . $url . '" class="text-primary font-weight-bold">' . $userName . '</a>';
            })
            ->addColumn('plan_details', function ($row) use ($type) {
                $planMeta = $row->plan_meta ? json_decode($row->plan_meta, true) : null;
                switch ($type) {
                    case 0: // Pin
                        $pinCount = $planMeta['pin_count'] ?? '0';
                        return 'Pins • ' . $pinCount . ' pins';
                    case 1: // Boost
                        $title = $planMeta['title'] ?? '-';
                        $tag = $planMeta['tag'] ?? '-';
                        return trim($title . ' • ' . $tag);
                    case 2: // Ghost
                        $title = $planMeta['title'] ?? '-';
                        $tag = $planMeta['tag'] ?? '-';
                        $duration = $planMeta['duration'] ?? '-';
                        $durationLabel = $this->humanizeDuration($duration);
                        return '<h5 class="mb-1">' . htmlspecialchars($title) . '</h5>' .
                               '<small class="text-muted">' . htmlspecialchars($tag) . '</small><br>' .
                               '<span class="badge badge-info mt-1">' . htmlspecialchars($durationLabel) . '</span>';
                    default:
                        return 'Unknown';
                }
            })
            ->addColumn('amount', function ($row) use ($type) {
                $planMeta = $row->plan_meta ? json_decode($row->plan_meta, true) : null;
                switch ($type) {
                    case 2: // Ghost
                        $amt = (float) ($planMeta['amount'] ?? 0);
                        $cur = $planMeta['currency'] ?? 'CHF';
                        return number_format($amt, 2) . ' ' . $cur;
                    case 1: // Boost => amount (per boost) * boost_count (from transactions)
                        $unit = (float) ($planMeta['amount'] ?? 0);
                        // $count = (int) ($row->boost_count ?? 0);
                        $count = (int) (1 ?? 0);
                        $total = $unit * max($count, 1);
                        return number_format($total, 2) . ' CHF';
                    case 0: // Pin
                        $amt = (float) ($planMeta['amount'] ?? 0);
                        return number_format($amt, 2) . ' CHF';
                    default:
                        return '-';
                }
            })
            ->addColumn('payment_status', function ($row) {
                $statusColors = [
                    'pending' => 'warning',
                    'succeeded' => 'success',
                    'failed' => 'danger',
                    'refunded' => 'info'
                ];
                $status = $row->payment_status ?? 'pending';
                $color = $statusColors[$status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . ucfirst($status) . '</span>';
            })
            ->addColumn('created_at', function ($row) {
                return convertTimezone( $row->created_at, null,  'Y-m-d H:i:s' );
            })
            ->addColumn('boost_duration', function ($row) use ($type) {
                if ($type === 1) { // Boost type
                    return '<span class="badge badge-warning">30 minutes</span>';
                }
                return '-';
            })
            ->addColumn('start_time', function ($row) use ($type) {
                if ($type === 2 && $row->start_time) {
                    return Carbon::parse($row->start_time)->format('Y-m-d H:i:s');
                }
                return '-';
            })
            ->addColumn('end_time', function ($row) use ($type) {
                if ($type === 2 && $row->end_time) {
                    return Carbon::parse($row->end_time)->format('Y-m-d H:i:s');
                }
                return '-';
            })
            ->addColumn('platform', function ($row) {
                if ($row->platform === 0) return 'Android';
                if ($row->platform === 1) return 'iOS';
                return '-';
            })
            ->addColumn('transaction_id', fn($row) => $row->transaction_id ?? '-')
            ->rawColumns(['payment_status', 'plan_details', 'name', 'boost_duration'])
            ->make(true);
    }
    private function updateAllPlanMetaInDb(): int
{
    $count = 0;

    $typeTableMap = [
        0 => 'pin_management',
        1 => 'boost_management',
        2 => 'ghost_management',
    ];

    $transactions = \DB::table('transactions')->whereNull('plan_meta')->get();

    foreach ($transactions as $transaction) {
        $table = $typeTableMap[$transaction->type] ?? null;
        $planMeta = null;

        if ($table) {
            $plan = \DB::table($table)
                ->where('id', $transaction->plan_id)
                ->first();

            if ($plan) {
                $planMeta = json_encode((array) $plan);
            }
        }

        $count += \DB::table('transactions')
            ->where('id', $transaction->id)
            ->update(['plan_meta' => $planMeta]);
    }

    return $count;
}

}
