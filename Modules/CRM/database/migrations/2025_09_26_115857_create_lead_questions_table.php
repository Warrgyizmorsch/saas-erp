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
        Schema::create('lead_questions', function (Blueprint $table) {
            $table->id();
            $table->string('field_name', 191)->unique();   // e.g., passport_number
            $table->string('label');                 // e.g., Passport Number
            $table->boolean('is_active')->default(1); // allow/disallow
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_questions');
    }
};
