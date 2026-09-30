<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Lightweight returning-visitor auth: lets a lead look up their own
            // past chat history by phone/email + this password, without a full
            // account system (registration, email verification, password reset).
            $table->string('password')->nullable()->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
