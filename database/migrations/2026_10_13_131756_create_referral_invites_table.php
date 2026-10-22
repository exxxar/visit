<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referral_invites', function (Blueprint $table) {
            $table->id();

            // нормализованный номер реферера (11 цифр, начинается с 7)
            $table->string('referrer_phone', 11)->index();

            // user_id реферера — заполнится когда он зарегистрируется
            $table->foreignId('referrer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // кто пришёл (реферал)
            $table->foreignId('referred_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // роль реферала
            $table->enum('role', ['client', 'tech_partner', 'ad_partner'])->default('client');

            // если клиент — id созданного заведения
            $table->foreignId('place_id')
                ->nullable()
                ->constrained('places')
                ->nullOnDelete();

            // статус
            $table->enum('status', ['pending', 'active', 'completed'])
                ->default('pending');

            // мета-данные (ip, ua, город и т.д.)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['referrer_phone', 'referrer_id']);
            $table->index('referred_user_id');
        });

        // добавим телефон в users (если ещё нет)
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->unique()->after('email');
                // нормализованный номер для поиска (11 цифр)
                $table->string('phone_normalized', 11)->nullable()->index()->after('phone');
            });
        }

        // добавим referred_by в users
        if (!Schema::hasColumn('users', 'referred_by')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('referred_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        // добавим referred_by в places (для клиентов)
        if (!Schema::hasColumn('places', 'referred_by')) {
            Schema::table('places', function (Blueprint $table) {
                $table->foreignId('referred_by')
                    ->nullable()
                    ->after('owner_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // 1. Сначала удаляем таблицу, которая ссылается на другие
        Schema::dropIfExists('referral_invites');

        // 2. Удаляем referred_by из places
        Schema::table('places', function (Blueprint $table) {
            if (Schema::hasColumn('places', 'referred_by')) {
                try {
                    $table->dropForeign('places_referred_by_foreign');
                } catch (\Exception $e) {}
                $table->dropColumn('referred_by');
            }
        });

        // 3. Удаляем колонки из users
        Schema::table('users', function (Blueprint $table) {
            // Сначала удаляем внешний ключ, если он есть
            try {
                $table->dropForeign('users_referred_by_foreign');
            } catch (\Exception $e) {}

            // Потом удаляем колонки
            foreach (['phone', 'phone_normalized', 'referred_by', 'meta'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
