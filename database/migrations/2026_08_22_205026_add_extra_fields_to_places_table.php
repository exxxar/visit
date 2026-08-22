<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            if (!Schema::hasColumn('places', 'external_id')) {
                $table->string('external_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('places', 'external_source')) {
                $table->string('external_source')->nullable()->after('external_id');
            }

            // защита от дублей при повторном импорте
            if (!Schema::hasIndex('places', 'places_external_unique')) {
                $table->unique(
                    ['external_source', 'external_id'],
                    'places_external_unique'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropUnique('places_external_unique');
            $table->dropColumn(['external_id', 'external_source']);
        });
    }
};
