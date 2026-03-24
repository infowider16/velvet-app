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
        // List only the tables you want to enable for translation right now
        $tables = [
            'ghost_management' => ['tag', 'title', 'duration'],
            'boost_management' => ['tag','title'],
            'pin_management' => ['tag','title'],
            'mobile_notifications' => ['title','body'],
            'interests' => ['name'],
        ];

        foreach ($tables as $table => $columns) {
            Schema::table($table, function (Blueprint $tableInstance) use ($table, $columns) {
                foreach ($columns as $column) {
                    $field = "{$column}_translation";
                    if (!Schema::hasColumn($table, $field)) {
                        $tableInstance->json($field)->nullable()->after($column);
                    }
                }
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'ghost_management' => ['tag', 'title', 'duration'],
            'boost_management' => ['tag','title'],
            'pin_management' => ['tag','title'],
            'mobile_notifications' => ['title','body'],
            'interests' => ['name'],
        ];
        foreach ($tables as $table => $columns) {
            Schema::table($table, function (Blueprint $tableInstance) use ($table, $columns) {
                foreach ($columns as $column) {
                    $field = "{$column}_translation";
                    if (Schema::hasColumn($table, $field)) {
                        $tableInstance->dropColumn($field);
                    }
                }
            });
        }
    }
};
