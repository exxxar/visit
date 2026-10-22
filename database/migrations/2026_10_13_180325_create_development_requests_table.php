<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('development_requests', function (Blueprint $table) {
            $table->id();

            // клиент, который заказал
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // заведение, для которого заказ (если есть)
            $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();

            // тип услуги
            $table->enum('service', [
                'app_food', 'app_beauty', 'app_fitness',
                'app_auto', 'app_blog', 'landing',
            ]);

            // заголовок и стоимость
            $table->string('title', 200);
            $table->decimal('amount', 10, 2)->default(0);

            // статус
            $table->enum('status', ['new', 'claimed', 'in_progress', 'completed', 'cancelled'])
                ->default('new');

            // тех. партнёр, который взял в работу
            $table->foreignId('tech_partner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // требования и комментарии
            $table->text('requirements')->nullable();

            // мета-данные (доп. информация, контакты)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['status', 'service']);
            $table->index('tech_partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_requests');
    }
};
