<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scout_errors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('hash')->unique(); // For grouping similar errors
            $table->string('type'); // Exception class name
            $table->text('message');
            $table->string('file');
            $table->integer('line');
            $table->longText('trace');
            $table->string('level')->default('error'); // error, critical, warning
            $table->string('environment')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->text('request_data')->nullable();
            $table->string('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->integer('occurrences')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->string('status')->default('unresolved'); // unresolved, resolved, ignored
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by')->nullable();
            
            $table->index('hash');
            $table->index('type');
            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scout_errors');
    }
};