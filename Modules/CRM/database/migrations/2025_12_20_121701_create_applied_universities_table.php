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
        Schema::create('applied_universities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('course_id');
            $table->string('course_title');
            $table->string('course_level')->nullable();
            $table->string('duration')->nullable();
            $table->string('location')->nullable();

            $table->decimal('tuition_fees', 10, 2)->nullable();
            $table->decimal('application_fees', 10, 2)->nullable();

            $table->string('university_id');
            $table->string('university_name');
            $table->string('currency_symbol', 10)->nullable();
            $table->string('currency_code', 10)->nullable();

            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'course_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_universities');
    }
};
