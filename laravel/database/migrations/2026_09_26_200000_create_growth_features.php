<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->json('amenities')->nullable()->after('hours');
            $table->json('languages')->nullable()->after('amenities');
            $table->json('payment_methods')->nullable()->after('languages');
            $table->timestamp('last_verified_at')->nullable()->after('is_verified');
        });

        Schema::create('business_claims', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->nullable(); $table->string('proof_url')->nullable();
            $table->text('note')->nullable(); $table->string('status')->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps(); $table->unique(['business_id', 'user_id']);
        });

        Schema::create('business_reports', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason'); $table->text('details')->nullable();
            $table->string('status')->default('pending'); $table->timestamps();
            $table->timestamp('reviewed_at')->nullable();
        });

        Schema::create('business_inquiries', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); $table->string('name'); $table->string('phone');
            $table->string('email')->nullable(); $table->timestamp('preferred_at')->nullable();
            $table->text('message')->nullable(); $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('deals', function (Blueprint $table) {
            $table->id(); $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->text('description')->nullable();
            $table->string('code')->nullable(); $table->string('discount_label')->nullable();
            $table->string('image')->nullable(); $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable(); $table->boolean('is_active')->default(true);
            $table->timestamps(); $table->index(['is_active', 'ends_at']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id(); $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); $table->text('description')->nullable();
            $table->string('venue')->nullable(); $table->string('image')->nullable();
            $table->timestamp('starts_at'); $table->timestamp('ends_at')->nullable();
            $table->decimal('price', 10, 2)->nullable(); $table->string('currency', 3)->default('EUR');
            $table->string('booking_url')->nullable(); $table->string('status')->default('published');
            $table->timestamps(); $table->index(['status', 'starts_at']);
        });

        Schema::create('activity_events', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event'); $table->json('metadata')->nullable();
            $table->string('session_id')->nullable(); $table->timestamps();
            $table->index(['event', 'created_at']);
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->text('body'); $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications'); Schema::dropIfExists('activity_events');
        Schema::dropIfExists('events'); Schema::dropIfExists('deals');
        Schema::dropIfExists('business_inquiries'); Schema::dropIfExists('business_reports');
        Schema::dropIfExists('business_claims');
        Schema::table('businesses', fn (Blueprint $table) => $table->dropColumn(['amenities','languages','payment_methods','last_verified_at']));
    }
};
