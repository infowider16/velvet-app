<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_management', function (Blueprint $table) {
            // new columns
            $table->string('tag')->nullable()->after('id');
            $table->string('title')->nullable()->after('tag');

            // rename column
            $table->renameColumn('price', 'amount');
        });
    }

    public function down(): void
    {
        Schema::table('boost_management', function (Blueprint $table) {
            // rollback rename
            $table->renameColumn('amount', 'price');

            // rollback new columns
            $table->dropColumn(['tag', 'title']);
        });
    }
};
