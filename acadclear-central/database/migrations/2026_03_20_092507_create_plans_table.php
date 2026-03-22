<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Standard, Enterprise
            $table->string('slug')->unique(); // basic, standard, enterprise
            $table->decimal('price', 10, 2); // 1500, 3000, 0 for custom
            $table->integer('max_students')->nullable(); // 500, 2000, null for unlimited
            $table->boolean('has_advanced_reports')->default(false);
            $table->boolean('has_multi_campus')->default(false);
            $table->boolean('has_custom_branding')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->json('features')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};