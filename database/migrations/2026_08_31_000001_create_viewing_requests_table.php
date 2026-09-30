<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable(); // agents.id — the assigned "task"
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, confirmed, completed, cancelled
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewing_requests');
    }
};
