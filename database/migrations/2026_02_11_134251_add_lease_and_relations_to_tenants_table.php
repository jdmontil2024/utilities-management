<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Add foreign keys
            $table->foreignId('unit_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('building_id')->after('unit_id')->constrained()->onDelete('cascade');
            
            // Add emergency contact relation
            $table->string('emergency_contact_relation')->after('emergency_contact_phone')->nullable();
            
            // Add lease information - FIXED: Removed extra quote from security_deposit
            $table->date('lease_start_date')->after('annual_income');
            $table->date('lease_end_date')->after('lease_start_date');
            $table->decimal('monthly_rent', 10, 2)->after('lease_end_date');
            $table->decimal('security_deposit', 10, 2)->after('monthly_rent')->nullable(); // FIXED: Removed extra quote
            $table->enum('lease_status', ['active', 'expired', 'pending', 'terminated'])->after('security_deposit')->default('active');
            $table->string('lease_agreement_path')->after('lease_status')->nullable();
            
            // Add additional occupants
            $table->integer('number_of_occupants')->after('lease_agreement_path')->default(1);
            $table->json('additional_occupants')->after('number_of_occupants')->nullable();
            
            // Add indexes
            $table->index(['building_id', 'lease_status']);
            $table->index(['unit_id', 'lease_status']);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['building_id']);
            
            // Drop indexes
            $table->dropIndex(['building_id', 'lease_status']);
            $table->dropIndex(['unit_id', 'lease_status']);
            
            // Drop columns
            $table->dropColumn([
                'unit_id',
                'building_id',
                'emergency_contact_relation',
                'lease_start_date',
                'lease_end_date',
                'monthly_rent',
                'security_deposit',
                'lease_status',
                'lease_agreement_path',
                'number_of_occupants',
                'additional_occupants'
            ]);
        });
    }
};