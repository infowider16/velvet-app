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
        Schema::table('ghost_management', function (Blueprint $table) {
             // Remove old columns
            if (Schema::hasColumn('ghost_management', 'no_of_days')) {
                $table->dropColumn('no_of_days');
            }
            if (Schema::hasColumn('ghost_management', 'cost')) {
                $table->dropColumn('cost');
            }

            // Add new columns
            $table->string('tag')->nullable()->after('id');
            $table->string('title')->after('tag');
            $table->string('duration')->after('title'); 
            $table->decimal('amount', 10, 2)->after('duration');
            $table->string('currency', 10)->default('CHF')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghost_management', function (Blueprint $table) {
            // Add back old columns
            $table->integer('no_of_days')->nullable();
            $table->decimal('cost', 10, 2)->nullable();

            // Drop new columns
            $table->dropColumn(['tag', 'title', 'duration', 'amount', 'currency', 'type']);
        });
    }
};
