<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/xxxx_xx_xx_add_verified_lead_to_leads_table.php
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('verified_lead')->default(false)->after('uid');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('verified_lead');
        });
    }
};
