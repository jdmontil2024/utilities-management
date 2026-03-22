<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->foreignId('floor_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('unit_number');
            $table->string('unit_name')->nullable();
            $table->integer('floor');
            $table->decimal('area', 10, 2);
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->string('unit_type');
            $table->string('status')->default('vacant');
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->decimal('parking_fee', 8, 2)->default(0);
            $table->json('amenities')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->integer('year_renovated')->nullable();
            $table->date('available_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['building_id', 'unit_number']);
            $table->index(['status', 'available_date']);
            $table->index('floor');
            $table->index('unit_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};