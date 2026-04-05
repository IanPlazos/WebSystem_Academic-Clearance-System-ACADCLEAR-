<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_slug')->nullable();
            $table->string('tenant_name')->nullable();
            $table->string('plan_name');
            $table->string('institution_name');
            $table->string('contact_person');
            $table->string('email');
            $table->string('contact_number', 40);
            $table->enum('payment_method', ['gcash', 'bank']);
            $table->string('amount', 50);
            $table->string('payment_reference')->nullable();
            $table->string('gcash_number', 40)->nullable();
            $table->string('bank_name', 120)->nullable();
            $table->string('bank_account_name', 120)->nullable();
            $table->string('bank_account_number', 80)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['tenant_slug']);
            $table->index(['email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_requests');
    }
};
