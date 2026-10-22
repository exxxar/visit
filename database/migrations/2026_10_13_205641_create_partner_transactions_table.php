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
        Schema::create('partner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['earning', 'withdrawal', 'bonus', 'adjustment']);
            $table->decimal('amount', 10, 2); // Для вывода - отрицательное или положительное, но лучше хранить модуль и тип
            $table->string('description')->nullable();
            $table->foreignId('request_id')->nullable()->constrained('promotion_requests')->nullOnDelete(); // Связь с заказом
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_transactions');
    }
};
