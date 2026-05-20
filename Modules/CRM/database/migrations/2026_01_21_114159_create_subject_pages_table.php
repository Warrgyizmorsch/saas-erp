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
        Schema::create('subject_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('images')->nullable();
            $table->string('slug', 191)->unique();

            $table->enum('status', ['draft', 'publish'])->default('draft');

            $table->longText('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('schema')->nullable();
            $table->longText('faq')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_pages');
    }
};
