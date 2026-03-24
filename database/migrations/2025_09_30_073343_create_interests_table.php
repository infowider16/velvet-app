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
           Schema::create('interests', function (Blueprint $table) {
            $table->id(); 
            $table->integer('parent_id')->default(0)->nullable(); // Parent interest ID for hierarchical structure
            $table->string('name');      // Interest name
            $table->timestamps();        // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
