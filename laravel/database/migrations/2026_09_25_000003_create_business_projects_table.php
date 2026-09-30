<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->json('images')->nullable();

            $table->enum('property_type', ['apartment', 'villa', 'land', 'commercial', 'other'])->default('other');
            $table->enum('listing_type', ['sale', 'rent', 'presale'])->default('sale');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('GBP');

            $table->unsignedInteger('area_m2')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('floor')->nullable();

            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'status']);
            $table->index(['status', 'listing_type', 'property_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_projects');
    }
};
