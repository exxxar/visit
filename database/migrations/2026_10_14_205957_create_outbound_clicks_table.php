<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outbound_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500)->index();
            $table->string('domain', 100)->index();
            $table->string('context', 50)->nullable()->index(); // place, social, partner, map, story, news
            $table->foreignId('place_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->string('referer', 500)->nullable();
            $table->string('page_url', 500)->nullable(); // страница, с которой кликнули
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_clicks');

    }
};
