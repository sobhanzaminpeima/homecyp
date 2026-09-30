<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_sources', function (Blueprint $table) {
            // Raw text for the "manual" source type (admin pastes content directly)
            $table->longText('content')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_sources', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
