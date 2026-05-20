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
        Schema::table('warr_leads', function (Blueprint $table) {
            $table->string('company_size')->nullable()->after('designation');
            $table->text('service_categories')->nullable()->after('company_size');
            $table->text('page_url')->nullable()->after('source');
            $table->text('comment')->nullable()->after('message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warr_leads', function (Blueprint $table) {
            $table->dropColumn([
                'company_size',
                'service_categories',
                'page_url',
                'comment',
            ]);
        });
    }
};
