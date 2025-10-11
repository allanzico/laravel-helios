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
        Schema::create('scout_scheduled_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('command');
            $table->string('expression')->nullable(); // e.g., '* * * * *'
            $table->string('status'); // e.g., starting, finished, failed
            $table->longText('output')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->float('runtime_ms')->nullable();
            $table->string('triggered_by')->after('status')->default('unknown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scout_scheduled_tasks');
    }
};
