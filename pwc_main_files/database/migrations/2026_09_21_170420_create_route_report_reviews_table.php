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
        Schema::create('route_report_reviews', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('route_id', 36)->nullable()->index();
            $table->string('week');
            $table->string('month');
            $table->string('year');
            $table->boolean('is_reviewed')->default(false);
            $table->unsignedBigInteger('reviewed_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_report_reviews');
    }
};
