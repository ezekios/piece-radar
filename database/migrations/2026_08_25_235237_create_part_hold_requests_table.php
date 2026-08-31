<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('part_hold_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('part_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('status', [
                'pending',
                'accepted',
                'refused',
                'cancelled',
                'completed',
                'expired'
            ])->default('pending');

            $table->text('customer_message')->nullable();
            $table->text('scrapyard_response')->nullable();

            $table->timestamp('reserved_until')->nullable();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('reserved_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('part_hold_requests');
    }
};
