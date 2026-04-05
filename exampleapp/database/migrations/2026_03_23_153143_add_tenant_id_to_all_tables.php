<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add tenant_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Add tenant_id to colleges table
        Schema::table('colleges', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Add tenant_id to departments table
        Schema::table('departments', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Add tenant_id to clearances table
        Schema::table('clearances', function (Blueprint $table) {
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('colleges', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('clearances', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
};