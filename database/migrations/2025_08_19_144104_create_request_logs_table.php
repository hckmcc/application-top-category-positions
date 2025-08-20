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
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('endpoint');
            $table->json('parameters')->nullable();
            $table->integer('response_status')->nullable();
            $table->string('response_message')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['ip_address'], 'idx_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_logs');
    }
};
