<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->foreignId('utility_type_id')->constrained()->onDelete('cascade');
            $table->decimal('current_reading', 12, 4);
            $table->decimal('previous_reading', 12, 4);
            $table->decimal('consumption', 12, 4);
            $table->date('reading_date');
            $table->string('reading_type');
            $table->foreignId('reader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('meter_number')->nullable();
            $table->boolean('is_billed')->default(false);
            $table->timestamps();
            
            $table->index(['unit_id', 'utility_type_id', 'reading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};