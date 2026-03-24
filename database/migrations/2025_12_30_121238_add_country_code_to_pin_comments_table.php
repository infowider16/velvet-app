<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pin_comments', function (Blueprint $table) {
            $table->string('country_code', 5)
                  ->nullable()
                  ->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pin_comments', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
};
