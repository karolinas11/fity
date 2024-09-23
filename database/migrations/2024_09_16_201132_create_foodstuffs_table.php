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
        Schema::create('foodstuffs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('amount');
            $table->string('measurement_unit');
            $table->double('calories');
            $table->double('proteins');
            $table->double('fats');
            $table->double('carbohydrates');
            $table->unsignedBigInteger('foodstuff_category_id');
            $table->timestamps();

            $table->foreign('foodstuff_category_id')->references('id')->on('foodstuff_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foodstuffs');
    }
};
