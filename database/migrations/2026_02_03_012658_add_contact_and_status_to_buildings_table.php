<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('contact_phone')->nullable()->after('description');
            $table->string('contact_email')->nullable()->after('contact_phone');
            $table->string('status')->default('active')->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn(['contact_phone', 'contact_email', 'status']);
        });
    }
};