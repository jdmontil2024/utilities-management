<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->decimal('cold_water_gallons', 12, 4);
            $table->decimal('hot_water_gallons', 12, 4)->nullable();
            $table->decimal('total_water_gallons', 12, 4);
            $table->decimal('water_pressure_psi', 6, 2)->nullable();
            $table->decimal('flow_rate_gpm', 8, 4)->nullable();
            $table->decimal('temperature_f', 5, 2)->nullable();
            $table->string('water_source')->nullable();
            $table->string('water_quality')->nullable();
            $table->json('sub_meter_readings')->nullable();
            $table->boolean('has_leak_detection')->default(false);
            $table->boolean('leak_detected')->default(false);
            $table->decimal('leak_rate_gph', 8, 4)->nullable();
            $table->decimal('sewage_gallons', 12, 4)->nullable();
            $table->decimal('stormwater_gallons', 12, 4)->nullable();
            $table->decimal('recycled_water_gallons', 12, 4)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('sewer_charge', 10, 2)->nullable();
            $table->decimal('stormwater_charge', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_readings');
    }
};