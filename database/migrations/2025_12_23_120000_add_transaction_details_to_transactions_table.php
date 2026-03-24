<?php



use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;



return new class extends Migration

{

    /**

     * Run the migrations.

     */

    public function up(): void

    {

        Schema::table('transactions', function (Blueprint $table) {

            // Add missing columns if they don't exist

            if (!Schema::hasColumn('transactions', 'currency')) {

                $table->string('currency', 10)->default('CHF');

            }

            if (!Schema::hasColumn('transactions', 'payment_status')) {

                $table->enum('payment_status', ['pending', 'succeeded', 'failed', 'refunded'])->default('succeeded')->after('currency');

            }

            if (!Schema::hasColumn('transactions', 'platform')) {

                $table->tinyInteger('platform')->default(0)->comment('0=Android, 1=iOS')->after('payment_status');

            }

        });

    }



    /**

     * Reverse the migrations.

     */

    public function down(): void

    {

        Schema::table('transactions', function (Blueprint $table) {

            if (Schema::hasColumn('transactions', 'amount')) {

                $table->dropColumn('amount');

            }

            if (Schema::hasColumn('transactions', 'currency')) {

                $table->dropColumn('currency');

            }

            if (Schema::hasColumn('transactions', 'payment_status')) {

                $table->dropColumn('payment_status');

            }

            if (Schema::hasColumn('transactions', 'platform')) {

                $table->dropColumn('platform');

            }

        });

    }

};

