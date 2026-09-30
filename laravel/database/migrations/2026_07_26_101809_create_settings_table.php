<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
        $table->engine = 'InnoDB';
            $table->id();
            $table->string('group_key');
            $table->string('item_key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['group_key', 'item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
