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
            $table->longText('meta_title')->nullable()->after('type');
            $table->text('meta_discribtion')->nullable()->after('meta_title');
            $table->string('meta_tag')->nullable()->after('meta_discribtion');
            $table->longText('schema')->nullable()->after('meta_tag');
            $table->json('faq')->nullable()->after('schema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title',
                'meta_discribtion',
                'meta_tag',
                'schema',
                'faq',
            ]);
        });
    }
};
