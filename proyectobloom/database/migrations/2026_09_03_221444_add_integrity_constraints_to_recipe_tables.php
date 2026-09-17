<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE recipe_has_likes
             MODIFY likes INT UNSIGNED NOT NULL DEFAULT 0'
        );

        Schema::table('recipe_has_likes', function (Blueprint $table) {
            $table->unique('recipes_id');
        });

        Schema::table('users_vote_recipes', function (Blueprint $table) {
            $table->unique(['users_id', 'recipes_id']);
        });

        Schema::table('users_save_recipes', function (Blueprint $table) {
            $table->unique(['users_id', 'recipes_id']);
        });
    }

    public function down(): void
    {
        Schema::table('recipe_has_likes', function (Blueprint $table) {
            $table->dropUnique(['recipes_id']);
        });

        Schema::table('users_vote_recipes', function (Blueprint $table) {
            $table->dropUnique(['users_id', 'recipes_id']);
        });

        Schema::table('users_save_recipes', function (Blueprint $table) {
            $table->dropUnique(['users_id', 'recipes_id']);
        });

        DB::statement(
            'ALTER TABLE recipe_has_likes
             MODIFY likes INT NOT NULL'
        );
    }
};