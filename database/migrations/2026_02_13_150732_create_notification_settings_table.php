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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();

            $table->string('type')
                ->comment('Notification type: new_messages, comments_on_pins, likes_on_pins, friend_requests, all_push (master toggle)');

            $table->boolean('is_enabled')
                ->default(true)
                ->comment('Indicates whether this notification type is enabled or disabled');

            $table->timestamps();

            $table->unique(['user_id', 'type']);

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
        Schema::dropIfExists('notification_settings');
    }
};
