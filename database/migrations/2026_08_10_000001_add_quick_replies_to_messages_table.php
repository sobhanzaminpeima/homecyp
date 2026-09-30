<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Distinct from suggested_questions: these are single-tap answer options for a
            // guided flow question (e.g. "Investment / Living / Holiday Home"), not follow-up
            // questions the user might separately want to ask.
            $table->json('quick_replies')->nullable()->after('suggested_questions');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('quick_replies');
        });
    }
};
