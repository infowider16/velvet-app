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
        Schema::create('muted_friends', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('friend_id');

            $table->timestamps();

            // Prevent duplicates and make lookups fast
            $table->unique(['user_id', 'friend_id']);

            // Optional extra indexes depending on query patterns
            $table->index(['user_id']);     // list muted friends
            $table->index(['friend_id']);   // reverse checks if needed

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('friend_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muted_friends');
    }
};
