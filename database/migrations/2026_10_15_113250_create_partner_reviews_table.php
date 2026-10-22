<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partner_reviews', function (Blueprint $table) {
            $table->id();

            // Кто оценивает
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();

            // Кого оценивают
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();

            // Что оценивают (полиморфная связь: PromotionRequest или DevelopmentRequest)
            $table->morphs('reviewable'); // reviewable_type + reviewable_id

            // Оценка
            $table->unsignedTinyInteger('rating')->comment('1-5');

            // Текст отзыва
            $table->text('comment')->nullable();

            // Критерии (опционально, для детальной оценки)
            $table->json('criteria')->nullable(); // {"communication": 5, "quality": 4, "deadline": 5}

            // Модерация
            $table->boolean('is_public')->default(true);
            $table->boolean('is_verified')->default(false); // проверено админом
            $table->string('admin_note', 500)->nullable();

            // Ответ партнёра
            $table->text('partner_reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();

            // Один отзыв на заявку от одного клиента
            $table->unique(['client_id', 'reviewable_type', 'reviewable_id']);

            // Индексы для быстрых выборок
            $table->index(['partner_id', 'is_public']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_reviews');
    }
};
