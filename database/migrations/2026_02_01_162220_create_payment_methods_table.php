<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('last_four');
            $table->string('card_type')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_type')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->json('billing_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('payment_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};