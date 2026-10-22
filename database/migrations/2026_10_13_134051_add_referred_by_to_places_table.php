<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'referred_by')) {
                $table->foreignId('referred_by')
                    ->nullable()
                    ->after('owner_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (Schema::hasColumn('places', 'referred_by')) {
                $table->dropForeign(['referred_by']);
                $table->dropColumn('referred_by');
            }
        });
    }
};
