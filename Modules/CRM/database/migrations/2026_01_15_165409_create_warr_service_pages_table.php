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
        Schema::create('warr_service_pages', function (Blueprint $table) {
            $table->id();

            // MAIN SERVICE of this page (one page = one main service)
            $table->foreignId('service_id')
                ->constrained('warr_services')
                ->cascadeOnDelete();

            // Location
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('warr_countries')
                ->nullOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->constrained('warr_cities')
                ->nullOnDelete();

            // Hero
            $table->string('hero_title');
            $table->text('hero_description')->nullable();

            // SECTION 1
            $table->string('section1_title')->nullable();
            $table->text('section1_description')->nullable();
            $table->json('section1_points')->nullable();

            // SECTION 2
            $table->string('section2_title')->nullable();
            $table->text('section2_description')->nullable();
            $table->json('section2_points')->nullable();

            // SECTION 3
            $table->string('section3_title')->nullable();
            $table->text('section3_description')->nullable();
            $table->json('section3_points')->nullable();

            // SECTION 4
            $table->string('section4_title')->nullable();
            $table->text('section4_description')->nullable();
            $table->json('section4_points')->nullable();

            // FAQ JSON: [{question:"", answer:""}]
            $table->json('faq')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Slug
            $table->string('slug')->unique();

            // Status
            $table->enum('status', ['draft', 'publish'])->default('draft');

            $table->timestamps();

            // Helpful indexes
            $table->index(['status', 'country_id', 'city_id']);
            $table->index(['service_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warr_service_pages');
    }
};
