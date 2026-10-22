<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outbound_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('domain', 100)->index();
            $table->string('context', 50)->nullable()->index();
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('unique_ips')->default(0);
            $table->timestamps();

            $table->unique(['date', 'domain', 'context']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_daily_stats');
    }
};
