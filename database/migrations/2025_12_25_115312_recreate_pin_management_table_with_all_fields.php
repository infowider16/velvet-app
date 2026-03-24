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
        // Drop the table if it exists
        Schema::dropIfExists('pin_management');
        
        // Create the table with all fields
        Schema::create('pin_management', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->nullable();
            $table->string('title')->nullable();
            $table->integer('pin_count');
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pin_management');
    }
};
