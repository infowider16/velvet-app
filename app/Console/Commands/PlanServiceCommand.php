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
        
        $boostTransactionIds = User::query()
            ->where('booster_ranking', '!=', 0)
            ->whereNotNull('boost')
            ->pluck('boost');

        if ($boostTransactionIds->isEmpty()) {
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
        if ($expiredUserIds->isEmpty()) {
            return;
        }
        
        User::query()
            ->whereIn('id', $expiredUserIds)
            ->update([
                'booster_ranking' => 0,
            ]);
        
    }
}
