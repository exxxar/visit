<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('place_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('1-5 stars');
            $table->text('content');
            $table->json('photos')->nullable(); // массив путей к фото
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('moderator_comment')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['place_id', 'status']);
            $table->index('user_id');
        });

        // Ответы владельца на отзывы
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('place_reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // владелец
            $table->text('content');
            $table->timestamps();
        });

        // Жалобы на отзывы
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('place_reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');
            $table->timestamps();

            $table->index(['review_id', 'status']);
        });

        // Добавляем поля в таблицу places для агрегации
        if (!Schema::hasColumn('places', 'reviews_count')) {
            Schema::table('places', function (Blueprint $table) {
                $table->unsignedInteger('reviews_count')->default(0)->after('rating');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');
        Schema::dropIfExists('review_replies');
        Schema::dropIfExists('place_reviews');

        if (Schema::hasColumn('places', 'reviews_count')) {
            Schema::table('places', function (Blueprint $table) {
                $table->dropColumn('reviews_count');
            });
        }
    }
};
