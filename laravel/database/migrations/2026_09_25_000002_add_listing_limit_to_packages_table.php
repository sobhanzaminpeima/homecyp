<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // How many projects/products/services a business on this package may
            // list. Null = unlimited. Businesses with no package (or none of
            // these packages) fall back to the free-tier limit of 3, enforced
            // in the dashboard controllers.
            $table->unsignedInteger('listing_limit')->nullable()->after('is_premium');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('listing_limit');
        });
    }
};
