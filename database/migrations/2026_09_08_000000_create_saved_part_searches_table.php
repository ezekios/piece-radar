<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_part_searches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('matched_part_id')
                ->nullable()
                ->constrained('parts')
                ->nullOnDelete();

            $table->string('vehicle_brand');
            $table->string('vehicle_model');
            $table->unsignedSmallInteger('vehicle_year')->nullable();

            $table->string('part_name');
            $table->string('part_category')->nullable();

            $table->enum('status', ['active', 'matched', 'closed'])
                ->default('active');
            $table->timestamp('matched_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'matched_part_id']);
            $table->index(['vehicle_brand', 'vehicle_model']);
            $table->index('part_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_part_searches');
    }
};
