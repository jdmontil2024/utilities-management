<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gas_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_reading_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->decimal('consumption_ccf', 12, 4);
            $table->decimal('consumption_therms', 12, 4)->nullable();
            $table->decimal('pressure_psi', 6, 2)->nullable();
            $table->decimal('flow_rate_cfh', 8, 4)->nullable();
            $table->decimal('temperature_f', 5, 2)->nullable();
            $table->decimal('calorific_value_btu_per_cf', 6, 2)->nullable();
            $table->string('gas_type')->default('natural');
            $table->json('appliance_usage')->nullable();
            $table->boolean('has_leak_detection')->default(false);
            $table->boolean('leak_detected')->default(false);
            $table->decimal('carbon_monoxide_ppm', 6, 2)->nullable();
            $table->decimal('methane_percentage', 5, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('delivery_charge', 10, 2)->nullable();
            $table->decimal('storage_charge', 10, 2)->nullable();
            $table->timestamps();
            
            $table->index(['unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gas_readings');
    }
};