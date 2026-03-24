<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

// Run this script with: php artisan tinker < database/scripts/update_plan_meta.php

// Update plan_meta for all transactions according to new mapping:
// type 0 = Pin, type 1 = Ghost, type 2 = Boost

$transactions = DB::table('transactions')->get();

foreach ($transactions as $transaction) {
    $planMeta = null;
    if ($transaction->type == 0) {
        $plan = DB::table('pin_management')->where('id', $transaction->plan_id)->first();
        $planMeta = $plan ? json_encode((array) $plan) : null;
    } elseif ($transaction->type == 1) {
        $plan = DB::table('ghost_management')->where('id', $transaction->plan_id)->first();
        $planMeta = $plan ? json_encode((array) $plan) : null;
    } elseif ($transaction->type == 2) {
        $plan = DB::table('boost_management')->where('id', $transaction->plan_id)->first();
        $planMeta = $plan ? json_encode((array) $plan) : null;
    }
    DB::table('transactions')->where('id', $transaction->id)->update(['plan_meta' => $planMeta]);
}

echo "plan_meta updated for all transactions.\n";
