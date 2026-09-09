<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'scrapyard', 'admin', 'professional') NOT NULL DEFAULT 'client'");

            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['client', 'scrapyard', 'admin', 'professional'])
                ->default('client')
                ->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('users')
                ->where('role', 'professional')
                ->update(['role' => 'client']);

            DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'scrapyard', 'admin') NOT NULL DEFAULT 'client'");

            return;
        }

        DB::table('users')
            ->where('role', 'professional')
            ->update(['role' => 'client']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['client', 'scrapyard', 'admin'])
                ->default('client')
                ->change();
        });
    }
};
