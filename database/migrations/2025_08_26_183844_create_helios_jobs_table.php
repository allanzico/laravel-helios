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
        Schema::create('helios_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status'); // e.g., running, processed, failed
            $table->longText('payload');
            $table->longText('exception')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helios_jobs');
    }
};
