<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('type'); // apartment, villa, penthouse, studio, commercial, office, land, hotel_apartment, townhouse
            $table->string('category'); // project, resale, daily_rental, long_term_rental
            $table->string('status')->default('active'); // active, sold, rented, pending
            $table->decimal('price', 15, 2)->nullable();
            $table->string('currency', 10)->default('GBP');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->string('area_unit', 10)->default('sqm');
            $table->boolean('has_parking')->default(false);
            $table->boolean('has_pool')->default(false);
            $table->boolean('has_gym')->default(false);
            $table->boolean('has_sea_view')->default(false);
            $table->string('payment_plan')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('location')->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('video_url')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->string('airbnb_url')->nullable();
            $table->boolean('is_airbnb')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('views')->default(0);
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->json('amenities')->nullable();
            $table->json('investment_benefits')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('property_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('faq')->nullable();
            $table->unique(['property_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_translations');
        Schema::dropIfExists('properties');
    }
};
