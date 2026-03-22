<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Check if column exists before adding
            if (!Schema::hasColumn('units', 'unit_name')) {
                $table->string('unit_name')->nullable()->after('unit_number');
            }
            
            if (!Schema::hasColumn('units', 'square_footage')) {
                $table->decimal('square_footage', 10, 2)->nullable()->after('unit_name');
            }
            
            if (!Schema::hasColumn('units', 'bedrooms')) {
                $table->integer('bedrooms')->nullable()->after('square_footage');
            }
            
            if (!Schema::hasColumn('units', 'bathrooms')) {
                $table->integer('bathrooms')->nullable()->after('bedrooms');
            }
            
            if (!Schema::hasColumn('units', 'monthly_rent')) {
                $table->decimal('monthly_rent', 10, 2)->nullable()->after('bathrooms');
            }
            
            if (!Schema::hasColumn('units', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->nullable()->after('monthly_rent');
            }
            
            if (!Schema::hasColumn('units', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('security_deposit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $columns = ['unit_name', 'square_footage', 'bedrooms', 'bathrooms', 'monthly_rent', 'security_deposit', 'is_available'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('units', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
