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
        Schema::create('leads', function (Blueprint $table) {
            $table->id(); // id
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('lead_bucket_id')->nullable();
            $table->string('lead_status')->nullable();
            $table->string('lead_owner')->nullable();
            $table->date('date')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('adset_name')->nullable();
            $table->string('ad_name')->nullable();
            $table->string('platform')->nullable();
            $table->string('whats_your_preferred_intake')->nullable();
            $table->string('highest_completed')->nullable();
            $table->boolean('any_academic_gap')->nullable(); 
            $table->string('budget')->nullable();
            $table->string('applying_country_for_a_visa')->nullable();
            $table->string('what_course_are_you_planning_to_study')->nullable();
            $table->string('uid')->nullable();

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
