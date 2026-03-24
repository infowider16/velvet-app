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
        Schema::table('group_reports', function (Blueprint $table) {
            // add new column
            $table->unsignedBigInteger('user_id')->nullable()->after('group_id');

            // make existing column nullable
            $table->unsignedBigInteger('group_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_reports', function (Blueprint $table) {
            // rollback user_id
            $table->dropColumn('user_id');

            // revert group_id to not nullable
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
        });
    }
};
