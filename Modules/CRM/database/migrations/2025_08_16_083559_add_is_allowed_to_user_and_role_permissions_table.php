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
        // Add is_allowed to user_permissions
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->boolean('is_allowed')->default(true)->after('menu_id');
        });

        // Add is_allowed to role_permissions
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->boolean('is_allowed')->default(true)->after('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn('is_allowed');
        });

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropColumn('is_allowed');
        });
    }
};
