<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });

        Schema::create('sponsor_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('property_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->integer('priority')->default(0);
            $table->string('target_region')->nullable();
            $table->string('target_property_type')->nullable();
            $table->decimal('target_budget_min', 15, 2)->nullable();
            $table->decimal('target_budget_max', 15, 2)->nullable();
            $table->json('target_languages')->nullable();
            $table->string('status')->default('active'); // active, paused, ended
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->timestamps();
        });

        Schema::create('recommendation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->json('trigger_keywords'); // e.g. ["kids","children","school"]
            $table->string('intent')->nullable(); // optional intent match, e.g. investment
            $table->string('boosted_attribute'); // e.g. near_school, high_roi, near_hospital, remote_work_ready
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('timeline_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable(); // null = default global timeline
            $table->string('key'); // choose_area, choose_property, reserve, contract, title_deed, residency
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('next_action')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject'); // welcome_message, suggested_questions, cta
            $table->json('variants'); // [{key, weight, content}]
            $table->string('status')->default('active'); // active, paused, ended
            $table->json('results')->nullable(); // impressions/conversions per variant
            $table->timestamps();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('purpose')->nullable(); // investment, living, holiday_home
            $table->string('preferred_region')->nullable();
            $table->integer('preferred_bedrooms')->nullable();
            $table->json('inferred_attributes')->nullable(); // from recommendation_rules matches
            $table->json('viewed_property_ids')->nullable();
            $table->string('language', 10)->default('en');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('area_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->longText('overview')->nullable();
            $table->json('highlights')->nullable(); // schools, hospitals, beaches, universities, restaurants
            $table->unique(['area_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('area_translations');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('ab_tests');
        Schema::dropIfExists('timeline_steps');
        Schema::dropIfExists('recommendation_rules');
        Schema::dropIfExists('sponsor_campaigns');
        Schema::dropIfExists('sponsors');
    }
};
