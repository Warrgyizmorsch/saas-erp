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
             $table->unsignedBigInteger('department_id')->nullable()->after('city');
             $table->unsignedBigInteger('authority_id')->nullable()->after('department_id');
             $table->string('status')->nullable()->after('authority_id');
             $table->date('date')->nullable()->after('status');
             $table->boolean('is_delete')->default(0)->after('date');
         });
     }
 
     /**
      * Reverse the migrations.
      */
     public function down(): void
     {
         Schema::table('users', function (Blueprint $table) {
             $table->dropColumn([
                 'department_id',
                 'authority_id',
                 'status',
                 'date',
                 'is_delete'
             ]);
         });
     }
 };

