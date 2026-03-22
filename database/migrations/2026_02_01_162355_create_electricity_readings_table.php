<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electricity_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->decimal('peak_consumption_kwh', 12, 4)->nullable();
            $table->decimal('off_peak_consumption_kwh', 12, 4)->nullable();
            $table->decimal('shoulder_consumption_kwh', 12, 4)->nullable();
            $table->decimal('power_factor', 5, 3)->nullable();
            $table->decimal('maximum_demand_kw', 10, 4)->nullable();
            $table->time('time_of_peak_demand')->nullable();
            $table->decimal('reactive_power_kvar', 12, 4)->nullable();
            $table->decimal('apparent_power_kva', 12, 4)->nullable();
            $table->json('voltage_readings')->nullable();
            $table->json('current_readings')->nullable();
            $table->decimal('frequency_hz', 5, 2)->nullable();
            $table->string('meter_type')->default('smart');
            $table->string('tariff_type')->nullable();
            $table->boolean('has_time_of_use')->default(false);
            $table->json('sub_meter_readings')->nullable();
            $table->decimal('consumption_kwh', 12, 4);
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('demand_charge', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electricity_readings');
    }
};