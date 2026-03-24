<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pin_mark_comments', function (Blueprint $table) {
            $table->id();
        
            $table->unsignedBigInteger('pin_mark_id');
            $table->unsignedBigInteger('user_id');
        
            $table->text('comment');
            $table->dateTime('commented_on'); // 👈 manual date-time
            $table->integer('total_like')->default(0);
            $table->tinyInteger('status')->default(1);
        
            $table->timestamps();
        
            $table->foreign('pin_mark_id')
                ->references('id')
                ->on('pin_marks')
                ->onDelete('cascade');
        
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('pin_mark_comments');
    }
};
