<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('frequency');
            $table->integer('interval_days')->nullable();
            $table->date('last_performed')->nullable();
            $table->date('next_due_date');
            $table->string('status')->default('scheduled');
            $table->decimal('estimated_duration_hours', 5, 2)->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->text('checklist')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_maintenances');
    }
};