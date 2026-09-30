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
        Schema::table('cities', function (Blueprint $table) {
            $table->string('name_fa')->nullable()->after('name_tr');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_fa')->nullable()->after('name_tr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('name_fa');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('name_fa');
        });
    }
};
