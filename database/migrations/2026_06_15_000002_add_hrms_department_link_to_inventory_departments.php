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
        if (Schema::hasTable('inventory_departments')) {
            if (!Schema::hasColumn('inventory_departments', 'hrms_department_id')) {
                Schema::table('inventory_departments', function (Blueprint $table) {
                    $table->unsignedBigInteger('hrms_department_id')->nullable()->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('inventory_departments')) {
            if (Schema::hasColumn('inventory_departments', 'hrms_department_id')) {
                Schema::table('inventory_departments', function (Blueprint $table) {
                    $table->dropColumn('hrms_department_id');
                });
            }
        }
    }
};
