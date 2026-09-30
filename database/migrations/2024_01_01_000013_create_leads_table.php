<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('type')->default('contact'); // contact, viewing, investment, meta, facebook, instagram
            $table->string('status')->default('new'); // new, contacted, qualified, follow_up, closed
            $table->text('message')->nullable();
            $table->string('source')->nullable(); // website, facebook, instagram, google
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('preferred_language', 10)->default('en');
            $table->json('meta_data')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('leads');
    }
};
