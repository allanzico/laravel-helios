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
        Schema::create('helios_scheduled_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('command');
            $table->string('expression')->nullable(); // e.g., '* * * * *'
            $table->string('status'); // e.g., starting, finished, failed
            $table->longText('output')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->float('runtime_ms')->nullable();
            $table->string('triggered_by')->default('unknown');

            $table->index(['status', 'started_at']);
            $table->index('finished_at');
            $table->index('command');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helios_scheduled_tasks');
    }
};
