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
        Schema::create('app_category_positions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('application_id');
            $table->integer('country_id');
            $table->integer('category_id');
            $table->integer('position');
            $table->date('date');
            $table->timestamps();

            $table->index(['application_id', 'country_id', 'category_id', 'date'], 'idx_app_country_category_date');

            $table->index(['application_id', 'country_id', 'date'], 'idx_app_country_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_category_positions');
    }
};
