<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('callback_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->text('message');
            $table->enum('status', ['sent', 'delivered', 'seen'])->default('sent');
            $table->string('bucket')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign Keys (agar relations define karna ho)
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('callback_messages');
    }
};
