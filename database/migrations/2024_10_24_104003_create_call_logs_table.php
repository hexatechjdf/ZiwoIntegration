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
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_id')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('call_direction')->nullable();
            $table->string('status')->nullable();
            $table->string('location_id')->nullable();
            $table->string('call_duration')->nullable();
            $table->string('location_phone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');
    }
};
