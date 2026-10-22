<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotion_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('place_id')->nullable()->constrained()->nullOnDelete();

            $table->string('service', 40);
            $table->string('title', 200);
            $table->decimal('amount', 10, 2)->default(0);

            $table->enum('status', ['new','claimed','in_progress','completed','cancelled'])
                ->default('new');

            $table->foreignId('partner_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->text('requirements')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'service']);
            $table->index('partner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_requests');
    }
};
