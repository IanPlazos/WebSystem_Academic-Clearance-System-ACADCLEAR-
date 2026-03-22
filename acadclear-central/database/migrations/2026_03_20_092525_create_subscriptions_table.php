<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained();
            $table->dateTime('starts_at')->useCurrent();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['tenant_id', 'status']);
            $table->index(['ends_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};