<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_history', function (Blueprint $table) {
            // Rename columns
            $table->renameColumn('start_date', 'start_date_time');
            $table->renameColumn('end_date', 'end_date_time');
        });

        Schema::table('boost_history', function (Blueprint $table) {
            // Change column types from DATE to DATETIME
            $table->dateTime('start_date_time')->change();
            $table->dateTime('end_date_time')->change();
        });
    }

    public function down(): void
    {
        Schema::table('boost_history', function (Blueprint $table) {
            // Revert column types
            $table->date('start_date_time')->change();
            $table->date('end_date_time')->change();
        });

        Schema::table('boost_history', function (Blueprint $table) {
            // Revert column names
            $table->renameColumn('start_date_time', 'start_date');
            $table->renameColumn('end_date_time', 'end_date');
        });
    }
};
