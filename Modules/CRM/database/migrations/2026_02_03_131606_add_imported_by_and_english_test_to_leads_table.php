<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            // English test info
            $table->string('english_test_status')
                ->nullable()
                ->after('highest_completed');

            // Who imported this lead
            $table->unsignedBigInteger('imported_by')
                ->nullable()
                ->after('platform');

            // Optional but recommended FK
            $table->foreign('imported_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['imported_by']);
            $table->dropColumn([
                'english_test_status',
                'imported_by',
            ]);
        });
    }
};
