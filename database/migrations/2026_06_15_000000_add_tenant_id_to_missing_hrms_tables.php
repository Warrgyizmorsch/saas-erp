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
        $tables = [
            'broadcasts',
            'employee_reviews',
            'employee_review_details',
            'job_requirements',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    Schema::table($table, function (Blueprint $tableSchema) {
                        $tableSchema->string('tenant_id')->nullable()->index();
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'broadcasts',
            'employee_reviews',
            'employee_review_details',
            'job_requirements',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $tableSchema) {
                    $tableSchema->dropColumn('tenant_id');
                });
            }
        }
    }
};
