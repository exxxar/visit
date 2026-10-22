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
        Schema::create('map_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layer_id')->constrained('map_layers')->cascadeOnDelete();

            // Связь с заведением (nullable). Если есть, берем данные оттуда. Если нет - используем свои поля.
            $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();

            // Собственные данные (используются, если place_id = null)
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Координаты
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);

            $table->decimal('x', 5, 2)->nullable()->comment('Позиция X в % для схемы');
            $table->decimal('y', 5, 2)->nullable()->comment('Позиция Y в % для схемы');

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_objects');
    }
};
