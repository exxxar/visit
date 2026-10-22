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

        Schema::create('map_layers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Например: "Стрит-арт", "Исторический центр"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#22d3ee'); // Цвет маркеров слоя
            $table->string('icon', 20)->default('📍'); // Эмодзи или иконка
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_layers');
    }
};
