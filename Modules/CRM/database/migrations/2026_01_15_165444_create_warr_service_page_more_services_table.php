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
        Schema::create('warr_service_page_more_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_page_id')
                ->constrained('warr_service_pages')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('warr_services')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['service_page_id', 'service_id'],
                'wsp_more_services_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warr_service_page_more_services');
    }
};
