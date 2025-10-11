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
        Schema::create('helios_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('method', 10);
            $table->text('uri');
            $table->string('controller_action')->nullable();
            $table->integer('status_code');
            $table->integer('duration_ms');
            $table->float('memory_mb', 8, 2);
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helios_requests');
    }
};
