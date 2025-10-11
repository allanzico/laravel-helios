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
        Schema::table('scout_requests', function (Blueprint $table) {
            $table->float('duration_ms')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scout_requests', function (Blueprint $table) {
            $table->integer('duration_ms')->change();
        });
    }
};