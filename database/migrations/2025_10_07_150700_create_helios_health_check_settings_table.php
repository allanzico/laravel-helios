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
        Schema::create('helios_health_check_settings', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->string('check_class')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helios_health_check_settings');
    }
};