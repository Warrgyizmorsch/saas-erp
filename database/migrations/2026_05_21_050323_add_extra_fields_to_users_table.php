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
        Schema::table('users', function (Blueprint $table) {

            $table->unsignedBigInteger('role_id')->nullable()->after('id');

            $table->string('country_code', 10)->nullable()->after('email');

            $table->string('contact_no', 20)->nullable()->after('country_code');

            $table->string('image')->nullable()->after('contact_no');

            $table->boolean('is_deleted')->default(false)->after('image');

            $table->string('city')->nullable()->after('is_deleted');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'role_id',
                'country_code',
                'contact_no',
                'image',
                'is_deleted',
                'city',
            ]);

        });
    }
};