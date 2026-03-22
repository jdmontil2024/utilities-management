<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_schedule_id')->constrained()->onDelete('cascade');
            $table->decimal('min_consumption', 12, 4)->nullable();
            $table->decimal('max_consumption', 12, 4)->nullable();
            $table->decimal('rate_per_unit', 12, 6);
            $table->string('unit');
            $table->string('time_period')->nullable();
            $table->string('season')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};