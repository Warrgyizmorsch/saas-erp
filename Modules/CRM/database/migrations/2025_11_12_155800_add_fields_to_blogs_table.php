<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->longText('content')->after('title');
            $table->string('images')->nullable()->after('content');
            $table->string('slug')->unique()->after('images');
            $table->string('type')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['title', 'content', 'images', 'slug', 'type']);
        });
    }
};
