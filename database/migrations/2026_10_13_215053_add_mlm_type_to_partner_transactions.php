<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Изменяем enum, добавляя 'mlm'
        DB::statement("ALTER TABLE partner_transactions MODIFY COLUMN type ENUM('earning', 'withdrawal', 'bonus', 'adjustment', 'mlm') NOT NULL");
    }

    public function down(): void
    {
        // Откат (опционально, если нужно будет удалить)
        DB::statement("ALTER TABLE partner_transactions MODIFY COLUMN type ENUM('earning', 'withdrawal', 'bonus', 'adjustment') NOT NULL");
    }
};
