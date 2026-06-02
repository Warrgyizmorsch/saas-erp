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
        Schema::create('project_stage_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('inventory_projects')
                ->cascadeOnDelete();
            $table->foreignId('stage_id')
                ->constrained('stages')
                ->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stage_timelines');
    }
};
