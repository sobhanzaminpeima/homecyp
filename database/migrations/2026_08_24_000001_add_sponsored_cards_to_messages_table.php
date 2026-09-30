<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // [{property_id, campaign_id}, ...] — always rendered alongside, never
            // instead of, organic property_cards, and always labeled (spec section 9).
            $table->json('sponsored_cards')->nullable()->after('property_cards');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('sponsored_cards');
        });
    }
};
