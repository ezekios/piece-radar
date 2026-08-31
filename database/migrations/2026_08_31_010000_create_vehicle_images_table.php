<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('path');
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

            $table->index(['vehicle_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_images');
    }
};
