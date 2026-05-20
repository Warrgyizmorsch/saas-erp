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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('site')->default('wts')->after('type');
            $table->index(['site', 'type']);
            $table->unique(['site', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropUnique(['site', 'slug']);
            $table->dropIndex(['site', 'type']);
            $table->dropColumn('site');
        });
    }
};
