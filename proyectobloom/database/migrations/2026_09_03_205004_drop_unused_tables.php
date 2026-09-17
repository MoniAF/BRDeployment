<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('featured_recipes');
        Schema::dropIfExists('results');
    }

    public function down(): void
    {
        Schema::create('featured_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipes_id')->constrained('recipes');
            $table->integer('status');
            $table->timestamps();
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categories_id')->constrained('categories');
            $table->decimal('weight', 8, 2);
            $table->decimal('height', 8, 2);
            $table->integer('imc');
            $table->string('current_condition');
            $table->timestamps();
        });
    }
};