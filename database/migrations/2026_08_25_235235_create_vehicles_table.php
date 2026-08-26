<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('scrapyard_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('stock_origin', ['existing_stock', 'new_arrival'])
                ->default('new_arrival');

            $table->string('license_plate')->nullable();

            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year')->nullable();

            $table->string('version')->nullable();
            $table->string('engine')->nullable();
            $table->string('fuel')->nullable();
            $table->string('color')->nullable();

            $table->unsignedInteger('mileage')->nullable();

            $table->enum('status', ['draft', 'verified', 'published', 'archived'])
                ->default('draft');

            $table->date('arrival_date')->nullable();

            $table->timestamps();

            $table->index('stock_origin');
            $table->index('status');
            $table->index('license_plate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
