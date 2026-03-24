<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pin_comments', function (Blueprint $table) {
            $table->id(); // auto increment primary key

            $table->unsignedBigInteger('user_id');
            $table->text('pin_message');

            $table->dateTime('commented_on');
            $table->tinyInteger('status')->default(1);

            $table->timestamps();

            // Optional but recommended
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_comments');
    }
};
