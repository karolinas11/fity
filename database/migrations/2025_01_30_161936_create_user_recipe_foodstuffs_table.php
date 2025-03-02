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
        Schema::create('user_recipe_foodstuffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_recipe_id');
            $table->unsignedBigInteger('foodstuff_id');
            $table->double('amount');
            $table->boolean('purchased')->default(false);
            $table->timestamps();

            $table->foreign('user_recipe_id')->references('id')->on('user_recipes')->onDelete('cascade');
            $table->foreign('foodstuff_id')->references('id')->on('foodstuffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_recipe_foodstuffs');
    }
};
