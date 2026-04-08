<?php

namespace App\Services;

use App\Repositories\Eloquent\BoostRepository;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class BoosterService
{
    protected BoostRepository $boostRepository;
    protected TransactionRepository $transactionRepository;
    protected UserRepositoryInterface $userRepo;

    public function __construct(
        BoostRepository $boostRepository,
        TransactionRepository $transactionRepository,
        UserRepositoryInterface $userRepo

    ) {
        $this->boostRepository = $boostRepository;
        $this->userRepo = $userRepo;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Store boost transaction (sample method)
     */
    public function toggleBoosterStatus(int $userId): ?object
    {
        try {
            return DB::transaction(function () use ($userId) {
                $user = $this->getUserOrFail($userId);

                // best practice: avoid negative values
                $this->consumeBoostAndActivateRanking($user);
                $this->decrementBoostCountIfPossible($user);

                $transaction = $this->getBoosterTransaction($user);

                [$startAt, $endAt] = $this->makeBoosterWindow('Europe/Zurich', 30);

                return $this->createBoostHistory($user, $transaction, $startAt, $endAt);
            });
        } catch (ValidationException $e) {
            throw $e; // controller handles validation response
        } catch (\Throwable $e) {
            Log::error('BoosterService::toggleBoosterStatus failed', [
                'error'   => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return null;
        }
    }

    /**
     * Private helpers (reusable)
     */

    private function getUserOrFail(int $userId)
    {
        // Prefer findOrFail if your repo supports it; otherwise throw a ModelNotFoundException yourself.
        $user = $this->userRepo->find($userId);

        if (!$user) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("User not found: {$userId}");
        }

        return $user;
    }

    private function decrementBoostCountIfPossible($user): void
    {
        if ((int) $user->boost_count <= 0) {
            return;
        }

        // atomic update to avoid race conditions
        $this->userRepo->update(
            ['id' => $user->id],
            ['boost_count' => DB::raw('GREATEST(boost_count - 1, 0)'),'booster_ranking' => 1]
        );
    }

    private function consumeBoostAndActivateRanking($user): bool
    {
        if ((int) $user->boost_count <= 0) {
            return false;
        }

        DB::table('users')->where('booster_ranking', '>', 0)->increment('booster_ranking');

        return true;
    }


    private function getBoosterTransaction($user): ?object
    {
        // if boost is nullable, just return null safely
        if (empty($user->boost)) {
            return null;
        }

        return $this->transactionRepository->getByWhere(['id' => $user->boost]);
    }

    private function makeBoosterWindow(string $tz, int $minutes): array
    {
        $start = Carbon::now($tz);
        $end   = $start->copy()->addMinutes($minutes);

        return [$start, $end];
    }

    private function createBoostHistory($user, ?object $transaction, Carbon $start, Carbon $end)
    {
        return $this->boostRepository->createHistoryBoost([
            'user_id'         => $user->id,
            'transaction_id'  => $user->boost ?? null,
            'plan_id'         => $transaction->plan_id ?? null,
            'start_date_time' => $start->toDateTimeString(), // store clean datetime string
            'end_date_time'   => $end->toDateTimeString(),
        ]);
    }

    public function checkActiveBoosterSwissTime(int $userId): array
    {
        try {
            $nowSwiss = $this->nowSwiss();

            $activeHistory = $this->boostRepository->findActiveBoostHistory($userId, $nowSwiss);
            $user = $this->userRepo->find($userId);
          
            $remainingTime = null;

            if ($activeHistory && !empty($activeHistory->end_date_time)) {
                $endSwiss = Carbon::parse($activeHistory->end_date_time, 'Europe/Zurich');

                $remainingSeconds = max(
                    0,
                    (int) $nowSwiss->diffInSeconds($endSwiss, false)
                );

                $remainingTime = $this->formatMinutesSeconds($remainingSeconds);
            }

            return [
                'user_id'   => $user->id,
                'timezone'  => 'Europe/Zurich',
                'now'       => $nowSwiss->toDateTimeString(),
                'is_active' => (bool) $activeHistory,
                'is_delete' => $user->is_delete ?? 0,
                'remaining' => $remainingTime,
                'data'      => $activeHistory ? [
                    'id'              => $activeHistory->id,
                    'transaction_id'  => $activeHistory->transaction_id,
                    'plan_id'         => $activeHistory->plan_id,
                    'start_date_time' => $activeHistory->start_date_time,
                    'end_date_time'   => $activeHistory->end_date_time,
                ] : null,
            ];
        } catch (\Throwable $e) {
            Log::error('BoosterService::checkActiveBoosterSwissTime failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return [
                'user_id'   => $userId,
                'timezone'  => 'Europe/Zurich',
                'is_active' => false,
                'remaining' => null,
                'error'     => 'Something went wrong',
            ];
        }
    }

    private function formatMinutesSeconds(int $totalSeconds): string
    {
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    private function nowSwiss(): Carbon
    {
        return Carbon::now('Europe/Zurich');
    }

    public function inactivateBoosterPlan(int $userId): bool
    {
        try {
            return DB::transaction(function () use ($userId) {

                $user = $this->userRepo->find($userId);

                if ((int) $user->boost_count === 0) {
                    $this->clearBoostAndRanking($user->id);
                } else {
                    $this->clearOnlyRanking($user->id);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('BoosterService::inactivateBoosterPlan failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function clearBoostAndRanking(int $userId): void
    {
        $this->userRepo->update(
            ['id' => $userId],
            [
                'boost'           => null,
                'booster_ranking' => 0,
            ]
        );
    }

    private function clearOnlyRanking(int $userId): void
    {
        $this->userRepo->update(
            ['id' => $userId],
            [
                'booster_ranking' => 0,
            ]
        );
    }
}
