<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE part_hold_requests
            MODIFY status ENUM('pending', 'accepted', 'refused', 'cancelled', 'completed', 'expired')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('part_hold_requests')
            ->where('status', 'expired')
            ->update(['status' => 'cancelled']);

        DB::statement("
            ALTER TABLE part_hold_requests
            MODIFY status ENUM('pending', 'accepted', 'refused', 'cancelled', 'completed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
