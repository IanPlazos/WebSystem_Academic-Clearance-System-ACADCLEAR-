<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // University name
            $table->string('slug')->unique(); // URL slug
            $table->string('domain')->unique(); // Full domain
            $table->string('database')->unique(); // Database name
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#4e73df');
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};