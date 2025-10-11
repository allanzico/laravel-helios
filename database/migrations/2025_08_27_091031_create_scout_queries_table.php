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
        Schema::create('scout_queries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('connection_name');
            $table->longText('sql');
            $table->json('bindings')->nullable();
            $table->float('time_ms');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_queries');
    }
};
