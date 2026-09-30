<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_services');
    }
};
