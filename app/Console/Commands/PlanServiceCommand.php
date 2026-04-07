<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{User,BoostHistory};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:plan-service-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // public function handle(): void
    // {
    //     $nowSwiss = now('Europe/Zurich');

    //     $boostTransactionIds = User::query()
    //         ->where('booster_ranking', '!=', 0)
    //         ->whereNotNull('boost')
    //         ->pluck('boost');

    //     if ($boostTransactionIds->isEmpty()) {
    //         return;
    //     }

    //     $expiredUserIds = BoostHistory::query()
    //         ->whereIn('transaction_id', $boostTransactionIds)
    //         ->where('end_date_time', '<', $nowSwiss)
    //         ->pluck('user_id')
    //         ->unique();
    //     Log::info($nowSwiss);
    //     if ($expiredUserIds->isEmpty()) {
    //         return;
    //     }
    //     User::query()
    //         ->whereIn('id', $expiredUserIds)
    //         ->update([
    //             'booster_ranking' => 0,
    //         ]);
    // }

    public function handle(): void
    {
        $nowSwiss = now('Europe/Zurich');
        Log::info('Handle method started', ['now' => $nowSwiss]);

        $boostTransactionIds = User::query()
            ->where('booster_ranking', '!=', 0)
            ->whereNotNull('boost')
            ->pluck('boost');
        
        Log::info('Retrieved boost transaction IDs', [
            'count' => $boostTransactionIds->count(),
            'ids' => $boostTransactionIds->toArray()
        ]);

        if ($boostTransactionIds->isEmpty()) {
            Log::info('No active boost transactions found, exiting');
            return;
        }

        $expiredUserIds = BoostHistory::query()
            ->whereIn('transaction_id', $boostTransactionIds)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('boost_history')
                    ->groupBy('transaction_id');
            })
            ->where('end_date_time', '<', $nowSwiss)
            ->pluck('user_id')
            ->unique()
            ->values();
        
        Log::info('Retrieved expired user IDs', [
            'count' => $expiredUserIds->count(),
            'user_ids' => $expiredUserIds->values()->toArray()
        ]);

        if ($expiredUserIds->isEmpty()) {
            Log::info('No expired users found, exiting');
            return;
        }
        
        $updatedCount = User::query()
            ->whereIn('id', $expiredUserIds)
            ->update([
                'booster_ranking' => 0,
            ]);
        
        Log::info('Updated users booster_ranking to 0', [
            'updated_count' => $updatedCount,
            'affected_user_ids' => $expiredUserIds->values()->toArray()
        ]);
    }
}
