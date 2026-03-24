<?php

namespace App\Services\Admin;

use App\Contracts\Services\PlanServiceInterface;
use App\Repositories\Eloquent\PlanRepository;
use App\Repositories\Eloquent\BoostRepository;
use App\Repositories\Eloquent\PinRepository;
use App\Repositories\Eloquent\TransactionRepository;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlanService implements PlanServiceInterface
{
    protected $planRepository;
    protected $boostRepository;
    protected $pinRepository;
    protected $transactionRepository;


    public function __construct(
        PlanRepository $planRepository,
        BoostRepository $boostRepository,
        PinRepository $pinRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->planRepository = $planRepository;
        $this->boostRepository = $boostRepository;
        $this->pinRepository = $pinRepository;
        $this->transactionRepository = $transactionRepository;
    }

    // Ghost Plan methods
    public function getAll()
    {
        try {
            return $this->planRepository->all();
        } catch (\Exception $e) {
            Log::error('PlanService::getAllGhosts - ' . $e->getMessage());
            return [];
        }
    }

    public function getById($id)
    {
        try {
            return $this->planRepository->find($id);
        } catch (\Exception $e) {
            Log::error('PlanService::getById - ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data)
    {
        try {
            return $this->planRepository->create($data);
        } catch (\Exception $e) {
            Log::error('PlanService::create - ' . $e->getMessage());
            return null;
        }
    }

    public function update($id, array $data)
    {
        try {
            return $this->planRepository->update(['id' => $id], $data);
        } catch (\Exception $e) {
            Log::error('PlanService::update - ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            // If the plan doesn't exist, nothing to delete; return true for idempotency.
            $plan = $this->planRepository->find($id);
            if (!$plan) {
                Log::info("PlanService::delete - Plan {$id} not found; nothing to delete.");
                return true;
            }

            // Fetch related transactions for this plan.
            $transactions = $this->transactionRepository->getAllByWhere(['plan_id' => $id]);

            // Normalize to collection to handle arrays or Eloquent Collections.
            $txs = collect($transactions);

            // If there are no transactions, delete the plan.
            if ($txs->isEmpty()) {
                return $this->planRepository->deleteData(['id' => $id]);
            }

            // Determine if any transaction is currently active.
            $now = Carbon::now();
            $hasActive = $txs->contains(function ($tx) use ($now) {
                // $tx is likely an Eloquent model with Carbon-casted attributes.
                $start = $tx->start_time ? Carbon::parse($tx->start_time) : null;
                $end = $tx->end_time ? Carbon::parse($tx->end_time) : null;

                return $start && $end && $start->lte($now) && $end->gte($now);
            });

           
            if ($hasActive) {
                return response()->json(['status' => 0, 'message' => "PlanService::delete - Plan {$id} has active subscription(s); cannot delete."], 500);
            }

            // No active transactions, safe to delete.
            $run = $this->planRepository->deleteData(['id' => $id]);
            if ($run) {
               return response()->json(['status' => 1, 'message' => 'Ghost Plan deleted successfully.']);
            } else {
               return response()->json(['status' => 0, 'message' => 'Failed to delete Ghost Plan.'], 500);

            }
            
        } catch (\Exception $e) {
            Log::error('PlanService::delete - ' . $e->getMessage());
            return false;
        }
    }

    // Boost Plan methods
    public function getAllBoosts()
    {
        try {
            return $this->boostRepository->all();
        } catch (\Exception $e) {
            Log::error('PlanService::getAllBoosts - ' . $e->getMessage());
            return [];
        }
    }

    public function getBoostById($id)
    {
        try {
            return $this->boostRepository->find($id);
        } catch (\Exception $e) {
            Log::error('PlanService::getBoostById - ' . $e->getMessage());
            return null;
        }
    }

    public function createBoost(array $data)
    {
        try {
            return $this->boostRepository->create($data);
        } catch (\Exception $e) {
            Log::error('PlanService::createBoost - ' . $e->getMessage());
            return null;
        }
    }

    public function updateBoost($id, array $data)
    {
        try {
            return $this->boostRepository->update(['id' => $id], $data);
        } catch (\Exception $e) {
            Log::error('PlanService::updateBoost - ' . $e->getMessage());
            return false;
        }
    }

    public function deleteBoost($id)
    {
        try {
            return $this->boostRepository->deleteData(['id' => $id]);
        } catch (\Exception $e) {
            Log::error('PlanService::deleteBoost - ' . $e->getMessage());
            return false;
        }
    }

    // Pin Plan methods
    public function getAllPins()
    {
        try {
            return $this->pinRepository->all();
        } catch (\Exception $e) {
            Log::error('PlanService::getAllPins - ' . $e->getMessage());
            return [];
        }
    }
    public function getPinById($id)
    {
        try {
            return $this->pinRepository->find($id);
        } catch (\Exception $e) {
            Log::error('PlanService::getPinById - ' . $e->getMessage());
            return null;
        }
    }
    public function createPin(array $data)
    {
        try {
            return $this->pinRepository->create($data);
        } catch (\Exception $e) {
            Log::error('PlanService::createPin - ' . $e->getMessage());
            return null;
        }
    }

    public function updatePin($id, array $data)
    {
        try {
            return $this->pinRepository->update(['id' => $id], $data);
        } catch (\Exception $e) {
            Log::error('PlanService::updatePin - ' . $e->getMessage());
            return false;
        }
    }

    public function deletePin($id)
    {
        try {
            return $this->pinRepository->deleteData(['id' => $id]);
        } catch (\Exception $e) {
            Log::error('PlanService::deletePin - ' . $e->getMessage());
            return false;
        }
    }



}
