<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('leases', 'move_in_date')) {
                $table->date('move_in_date')->nullable()->after('end_date');
            }
            
            if (!Schema::hasColumn('leases', 'move_out_date')) {
                $table->date('move_out_date')->nullable()->after('move_in_date');
            }
            
            if (!Schema::hasColumn('leases', 'payment_due_day')) {
                $table->integer('payment_due_day')->nullable()->after('security_deposit');
            }
            
            if (!Schema::hasColumn('leases', 'terms')) {
                $table->json('terms')->nullable()->after('lease_type');
            }
            
            if (!Schema::hasColumn('leases', 'utilities_included')) {
                $table->json('utilities_included')->nullable()->after('terms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $columns = [
                'move_in_date',
                'move_out_date',
                'payment_due_day',
                'terms',
                'utilities_included'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('leases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};