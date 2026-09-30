<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('business_projects', function (Blueprint $table) {
            $table->string('rental_period', 24)->nullable()->after('listing_type');
            $table->date('available_from')->nullable()->after('rental_period');
            $table->unsignedTinyInteger('max_guests')->nullable()->after('bathrooms');
            $table->unsignedSmallInteger('minimum_stay')->nullable()->after('max_guests');
            $table->json('amenities')->nullable()->after('floor');
            $table->string('booking_url')->nullable()->after('address');
            $table->index(['listing_type', 'rental_period', 'status']);
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('locale', 12)->default('en');
            $table->string('title')->nullable();
            $table->string('last_intent', 50)->nullable();
            $table->json('search_context')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('conversation_id');
            $table->foreign('conversation_id')->references('id')->on('ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->json('property_ids')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::table('business_projects', fn (Blueprint $table) => $table->dropColumn(['rental_period','available_from','max_guests','minimum_stay','amenities','booking_url']));
    }
};
