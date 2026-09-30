<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Rating brought in from an external source at import time (e.g. Google
            // ratings scraped for the North Cyprus directory import) — kept separate
            // from rating_avg/rating_count so recalculateRating() never wipes it out
            // when on-platform reviews are added/moderated. rating_avg/rating_count
            // become a blend of this plus real Review rows.
            $table->decimal('external_rating_avg', 3, 2)->default(0)->after('rating_count');
            $table->unsignedInteger('external_rating_count')->default(0)->after('external_rating_avg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['external_rating_avg', 'external_rating_count']);
        });
    }
};
