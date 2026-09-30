<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('developer')->nullable();
            $table->string('location')->nullable();
            $table->string('region')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('price_from', 15, 2)->nullable();
            $table->string('currency', 10)->default('GBP');
            $table->date('completion_date')->nullable();
            $table->string('status')->default('active'); // active, completed, upcoming
            $table->boolean('is_featured')->default(false);
            $table->integer('views')->default(0);
            $table->string('source_url')->nullable();
            $table->json('amenities')->nullable();
            $table->json('investment_benefits')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('faq')->nullable();
            $table->unique(['project_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_translations');
        Schema::dropIfExists('projects');
    }
};
