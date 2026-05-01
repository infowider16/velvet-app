<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->unsignedInteger('unread_count')
                  ->default(0)
                  ->after('group_id'); // adjust position if needed
        });
    }

    public function down(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            if (Schema::hasColumn('group_members', 'unread_count')) {
                $table->dropColumn('unread_count');
            }
        });
    }
};
