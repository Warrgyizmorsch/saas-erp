<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_attributes', function (Blueprint $table) {
            $table->foreignId('lead_question_id')
                  ->nullable()
                  ->constrained('lead_questions')
                  ->onDelete('cascade')
                  ->after('id'); // adjust position if needed
        });
    }

    public function down(): void
    {
        Schema::table('lead_attributes', function (Blueprint $table) {
            $table->dropForeign(['lead_question_id']);
            $table->dropColumn('lead_question_id');
        });
    }
};
