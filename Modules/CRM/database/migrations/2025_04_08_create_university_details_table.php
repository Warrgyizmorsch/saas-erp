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
        Schema::create('university_details', function (Blueprint $table) {
            $table->id();
            $table->integer('university_id')->unsigned()->unique();
            // TODO: Uncomment when university table is created
            // $table->foreign('university_id')->references('id')->on('university')->onDelete('cascade');

            // Overview Tab
            $table->longText('overview')->nullable();

            // Ranking Tab
            $table->string('global_ranking')->nullable();
            $table->string('country_ranking')->nullable();
            $table->integer('batch_strength')->nullable();
            $table->string('global_diversity')->nullable();

            // Cost of Living & Tuition
            $table->decimal('cost_of_living', 12, 2)->nullable();
            $table->decimal('tuition_fee_from', 12, 2)->nullable();
            $table->string('currency_code')->default('GBP');

            // Admission Tab
            $table->longText('admission_requirements')->nullable();
            $table->string('entry_requirements_url')->nullable();

            // Scholarship Tab
            $table->longText('scholarship_info')->nullable();
            $table->string('scholarship_url')->nullable();

            // Finances Tab
            $table->longText('finances_info')->nullable();
            $table->string('finances_url')->nullable();

            // Accommodation Tab
            $table->longText('accommodation_info')->nullable();
            $table->string('accommodation_url')->nullable();

            // FAQs Tab
            $table->longText('faq_content')->nullable();

            // Images
            $table->string('banner_image')->nullable();
            $table->string('thumbnail_image')->nullable();

            // Status
            $table->enum('status', ['draft', 'published'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_details');
    }
};
