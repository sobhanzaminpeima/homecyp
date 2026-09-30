<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('agent_persona_id')->nullable(); // agents.id acting as chat persona
            $table->string('locale', 10)->default('en');
            $table->string('title')->nullable();
            $table->json('memory')->nullable(); // budget, area, purpose, bedrooms, viewed properties...
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role'); // user, assistant, system
            $table->longText('content');
            $table->json('suggested_questions')->nullable();
            $table->json('property_cards')->nullable(); // property ids referenced in this response
            $table->json('widget')->nullable(); // structured tool output: roi, mortgage, residency, area_advisor, compare, timeline
            $table->string('intent')->nullable(); // property_search, investment, general, comparison, roi
            $table->tinyInteger('feedback')->nullable(); // 1 = up, -1 = down
            $table->timestamps();
        });

        Schema::create('knowledge_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // pdf, doc, url, manual
            $table->string('category')->nullable(); // area, legal, project, faq, article
            $table->string('file_path')->nullable();
            $table->string('source_url')->nullable();
            $table->string('status')->default('pending'); // pending, processing, indexed, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_source_id')->constrained()->cascadeOnDelete();
            $table->longText('full_text');
            $table->json('embedding')->nullable(); // vector as JSON array of floats
            $table->string('embedding_model')->nullable();
            $table->integer('chunk_index')->default(0);
            $table->timestamps();

            $table->fullText('full_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
        Schema::dropIfExists('knowledge_sources');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
