<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $t) {
            $t->string('external_id')->nullable()->after('status');
            $t->string('external_source')->nullable()->after('external_id');
            $t->unique(['external_source', 'external_id']);
        });

        Schema::table('places', function (Blueprint $t) {
            $t->string('external_id')->nullable()->after('status');
            $t->string('external_source')->nullable()->after('external_id');
            $t->unique(['external_source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', fn ($t) => $t->dropUnique(['external_source', 'external_id'])->dropColumn(['external_id', 'external_source']));
        Schema::table('places', fn ($t) => $t->dropUnique(['external_source', 'external_id'])->dropColumn(['external_id', 'external_source']));
    }
};
