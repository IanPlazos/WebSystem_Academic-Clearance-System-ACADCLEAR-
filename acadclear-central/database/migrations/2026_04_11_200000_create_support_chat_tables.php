<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_slug')->unique();
            $table->string('tenant_name')->nullable();
            $table->string('tenant_domain')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_conversation_id')->constrained('support_conversations')->cascadeOnDelete();
            $table->string('sender_type');
            $table->string('sender_name')->nullable();
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['support_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_conversations');
    }
};
