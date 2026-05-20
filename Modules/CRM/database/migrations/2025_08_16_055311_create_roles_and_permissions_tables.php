<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Roles Table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Routes Table
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('route_name', 150);
            $table->string('method', 10);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // Menus Table
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('icon', 100)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('set null');
        });

        // Role Permissions Table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });

        // User Permissions Table
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });

        // Update Users Table (Add Columns)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false)->after('password');
            $table->unsignedBigInteger('role_id')->nullable()->after('is_deleted');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['is_deleted', 'role_id']);
        });

        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('roles');
    }
};
