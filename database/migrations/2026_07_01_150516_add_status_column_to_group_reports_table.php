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

            /*
            * Add status column
            */
            $table->string('status')
                  ->default('Pending')
                  ->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_reports', function (Blueprint $table) {

            /*
            * Drop status column
            */
            $table->dropColumn('status');
        });
    }
};
