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
        Schema::create('pin_mark_likes', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('pin_mark_id');
            $table->unsignedBigInteger('user_id');
        
            $table->timestamps();
        
            // One like per user per pin
            $table->unique(['pin_mark_id', 'user_id'], 'unique_user_pin_like');
        
            $table->foreign('pin_mark_id')
                ->references('id')
                ->on('pin_marks')
                ->cascadeOnDelete();
        
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_mark_likes');
    }
};
