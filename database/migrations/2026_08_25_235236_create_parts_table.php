<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('category')->nullable();

            $table->string('reference')->nullable();
            $table->string('oem_reference')->nullable();

            $table->text('description')->nullable();

            $table->enum('condition', ['unknown', 'used_good', 'used_average', 'damaged'])
                ->default('unknown');

            $table->enum('status', [
                'to_check',
                'preparing',
                'available',
                'reserved',
                'sold',
                'unavailable'
            ])->default('to_check');

            $table->decimal('price', 10, 2)->nullable();

            $table->boolean('is_published')->default(false);

            $table->timestamps();

            $table->index('name');
            $table->index('category');
            $table->index('status');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
