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
        Schema::table('users', function (Blueprint $table) {
            // Drop the old global unique constraint on email
            $table->dropUnique('users_email_unique');

            // Create a new composite unique constraint on (email, tenant_id)
            $table->unique(['email', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique(['email', 'tenant_id']);

            // Recreate global unique constraint
            $table->unique('email');
        });
    }
};
