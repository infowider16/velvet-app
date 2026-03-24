<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->text('title_translation')->nullable()->after('title');
            $table->text('slug_translation')->nullable()->after('slug');
            $table->longText('description_translation')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'title_translation',
                'slug_translation',
                'description_translation'
            ]);
        });
    }
};