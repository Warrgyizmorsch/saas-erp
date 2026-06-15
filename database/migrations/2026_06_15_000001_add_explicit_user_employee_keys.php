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
        if (Schema::hasTable('employees')) {
            if (!Schema::hasColumn('employees', 'user_id')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->unsignedBigInteger('user_id')->nullable()->index();
                });
            }
        }

        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'employee_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('employee_id')->nullable()->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees')) {
            if (Schema::hasColumn('employees', 'user_id')) {
                Schema::table('employees', function (Blueprint $table) {
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasTable('users')) {
            if (Schema::hasColumn('users', 'employee_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('employee_id');
                });
            }
        }
    }
};
