<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drives which showcase tab (if any) businesses under this category
            // get in the dashboard and on their public profile: real estate /
            // construction get project listings, markets/shops get an optional
            // product catalog, salons/hotels/etc get a priced service list.
            $table->enum('content_type', ['none', 'projects', 'products', 'services'])
                ->default('none')
                ->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('content_type');
        });
    }
};
