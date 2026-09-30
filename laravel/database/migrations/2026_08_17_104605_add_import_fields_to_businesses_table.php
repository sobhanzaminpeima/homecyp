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
            $table->string('website_secondary')->nullable()->after('website');
            $table->text('notes')->nullable()->after('hours');
            $table->string('source')->nullable()->after('notes');

            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn(['website_secondary', 'notes', 'source']);
        });
    }
};
