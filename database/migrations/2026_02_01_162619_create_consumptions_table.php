<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('utility_type_id')->constrained()->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('consumption', 12, 4);
            $table->decimal('cost', 12, 2);
            $table->decimal('average_daily_consumption', 12, 4);
            $table->decimal('peak_consumption', 12, 4)->nullable();
            $table->date('peak_date')->nullable();
            $table->json('daily_breakdown')->nullable();
            $table->boolean('is_estimated')->default(false);
            $table->timestamps();
            
            $table->index(['unit_id', 'utility_type_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumptions');
    }
};